<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\WebhookEvent;
use App\Services\Email\EmailService;
use App\Services\Shopify\ShopifyOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStripeWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 60, 300, 900];

    public function __construct(
        public int $webhookEventId
    ) {}

    public function handle(
        ShopifyOrderService $shopifyOrderService,
        EmailService $emailService
    ): void {
        $event = WebhookEvent::find($this->webhookEventId);
        if (!$event || $event->status === WebhookEvent::STATUS_PROCESSED) {
            return;
        }

        $payload = $event->payload;
        $type = $payload['type'] ?? $event->topic;

        Log::info("Processing Stripe webhook event {$event->id} of type [{$type}]");

        try {
            if ($type === 'checkout.session.completed') {
                $session = $payload['data']['object'] ?? $payload;
                $metadata = $session['metadata'] ?? [];

                if (($metadata['type'] ?? '') === 'balance_settlement') {
                    $this->handleBalanceSettlement($session, $metadata, $shopifyOrderService, $emailService);
                } else {
                    $this->handleDepositCheckout($session, $metadata, $shopifyOrderService, $emailService);
                }
            } elseif ($type === 'charge.dispute.created') {
                $this->handleDisputeCreated($payload, $shopifyOrderService);
            } elseif ($type === 'invoice.payment_succeeded') {
                $this->handleSubscriptionInvoiceSucceeded($payload, $shopifyOrderService);
            }

            $event->markProcessed();
        } catch (\Throwable $e) {
            Log::error("Error processing Stripe webhook {$event->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $event->markFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Handle initial pre-order deposit payment from Stripe checkout (S2-T3).
     */
    protected function handleDepositCheckout(
        array $session,
        array $metadata,
        ShopifyOrderService $shopifyOrderService,
        EmailService $emailService
    ): void {
        $shopId = $metadata['shop_id'] ?? null;
        $shop = $shopId ? Shop::find($shopId) : null;
        if (!$shop) {
            Log::warning("Cannot process deposit checkout: shop_id missing or invalid in metadata.");
            return;
        }

        $email = $session['customer_details']['email'] ?? ($session['customer_email'] ?? 'customer@northwindceramics.com');
        $stripeCustomerId = $session['customer'] ?? 'cus_guest_' . bin2hex(random_bytes(4));

        $customer = Customer::firstOrCreate(
            ['shop_id' => $shop->id, 'email' => $email],
            ['stripe_customer_id' => $stripeCustomerId]
        );

        if (!$customer->stripe_customer_id || str_starts_with($customer->stripe_customer_id, 'cus_guest_')) {
            $customer->update(['stripe_customer_id' => $stripeCustomerId]);
        }

        $planId = $metadata['plan_id'] ?? null;
        $currency = strtoupper($metadata['currency'] ?? ($shop->currency ?? 'AUD'));
        $totalCents = (int) ($metadata['total_cents'] ?? ($session['amount_total'] ?? 0));
        $depositCents = (int) ($metadata['deposit_cents'] ?? ($session['amount_total'] ?? 0));
        $balanceCents = (int) ($metadata['balance_cents'] ?? max(0, $totalCents - $depositCents));

        $paymentIntentId = $session['payment_intent'] ?? ('pi_' . bin2hex(random_bytes(8)));

        // Guard against duplicate creation if job retried mid-execution
        $existingPayment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
        if ($existingPayment) {
            Log::info("Payment {$paymentIntentId} already recorded; skipping duplicate order creation.");
            return;
        }

        // Create Master Order
        $order = Order::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'plan_id' => $planId,
            'currency' => $currency,
            'total_amount' => $totalCents,
            'paid_amount' => $depositCents,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        // Record Initial Payment
        Payment::create([
            'order_id' => $order->id,
            'schedule_id' => null,
            'stripe_payment_intent_id' => $paymentIntentId,
            'amount' => $depositCents,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        $plan = $planId ? Plan::find($planId) : null;
        if ($plan && $plan->isInstallment()) {
            // S4-T1: Multi-step installment schedule generation
            app(\App\Services\Payments\InstallmentService::class)->generateSchedules($order, $plan);
        } else {
            // Single balance schedule for deposit pre-orders
            PaymentSchedule::create([
                'order_id' => $order->id,
                'sequence' => 1,
                'due_at' => now()->addDays(30),
                'amount' => $balanceCents,
                'status' => PaymentSchedule::STATUS_PENDING,
            ]);
        }

        // Provision Shopify Order
        $shopifyOrderGid = $shopifyOrderService->createDepositOrder($shop, [
            'email' => $email,
            'variant_id' => $metadata['variant_id'] ?? 1,
            'quantity' => (int) ($metadata['quantity'] ?? 1),
            'total_cents' => $totalCents,
            'deposit_cents' => $depositCents,
            'currency' => $currency,
            'title' => $metadata['title'] ?? 'Handmade Ceramics Pre-order',
        ]);

        if ($shopifyOrderGid) {
            $order->update(['shopify_order_id' => $shopifyOrderGid]);
        }

        // Synchronize Tags and Metafields (S2-T4)
        $shopifyOrderService->syncPaymentStatus($order);

        // Send Transactional Deposit Confirmation (S2-T7)
        $emailService->sendDepositConfirmation($order);
    }

    /**
     * Handle balance settlement payment through balance link (S2-T5).
     */
    protected function handleBalanceSettlement(
        array $session,
        array $metadata,
        ShopifyOrderService $shopifyOrderService,
        EmailService $emailService
    ): void {
        $scheduleId = $metadata['schedule_id'] ?? null;
        $schedule = $scheduleId ? PaymentSchedule::find($scheduleId) : null;
        if (!$schedule) {
            Log::warning("Cannot process balance settlement: schedule_id missing or not found.");
            return;
        }

        $order = $schedule->order;
        $amountPaid = (int) ($session['amount_total'] ?? $schedule->amount);
        $paymentIntentId = $session['payment_intent'] ?? ('pi_balance_' . bin2hex(random_bytes(8)));

        $schedule->update([
            'status' => PaymentSchedule::STATUS_SUCCEEDED,
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'schedule_id' => $schedule->id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'amount' => $amountPaid,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        $order->update([
            'paid_amount' => $order->paid_amount + $amountPaid,
            'payment_status' => Order::STATUS_PAID,
        ]);

        // Synchronize updated status to Shopify (S2-T4)
        $shopifyOrderService->syncPaymentStatus($order);

        // Send Paid Receipt (S2-T7)
        $emailService->sendBalancePaidReceipt($order);
    }

    /**
     * Handle Stripe dispute creation: flag order, add Shopify tag, and record audit log (S4-T6, US-13).
     */
    protected function handleDisputeCreated(array $payload, ShopifyOrderService $shopifyOrderService): void
    {
        $dispute = $payload['data']['object'] ?? $payload;
        $paymentIntentId = $dispute['payment_intent'] ?? null;
        if (empty($paymentIntentId)) {
            return;
        }

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->with('order.shop')->first();
        if (!$payment || !$payment->order) {
            Log::warning("Disputed payment intent {$paymentIntentId} not found in local database.");
            return;
        }

        $order = $payment->order;
        $order->payment_status = 'disputed';
        $order->save();

        // Flag order in Shopify
        $shopifyOrderService->addOrderTag($order, 'dispute-opened');

        \App\Models\AuditLog::record(
            shopId: $order->shop_id,
            action: 'dispute.opened',
            target: $order,
            meta: [
                'dispute_id' => $dispute['id'] ?? null,
                'amount' => $dispute['amount'] ?? 0,
                'reason' => $dispute['reason'] ?? 'unknown',
                'payment_intent_id' => $paymentIntentId,
            ]
        );

        Log::warning("Dispute opened for order #{$order->id} (PaymentIntent: {$paymentIntentId})");
    }

    /**
     * Handle recurring subscription renewal invoice payment succeeded (S5-T1, US-12).
     */
    protected function handleSubscriptionInvoiceSucceeded(array $payload, ShopifyOrderService $shopifyOrderService): void
    {
        $invoice = $payload['data']['object'] ?? $payload;
        $stripeSubId = $invoice['subscription'] ?? null;
        if (empty($stripeSubId)) {
            return;
        }

        $subscription = \App\Models\Subscription::where('stripe_subscription_id', $stripeSubId)
            ->with(['shop', 'customer', 'plan'])
            ->first();

        if (!$subscription) {
            Log::warning("Subscription {$stripeSubId} not found for renewal invoice.");
            return;
        }

        $periodEnd = $invoice['lines']['data'][0]['period']['end'] ?? ($invoice['period_end'] ?? null);
        $nextRenewal = $periodEnd ? \Carbon\Carbon::createFromTimestamp($periodEnd) : now()->addMonth();

        $subscription->update([
            'status' => \App\Models\Subscription::STATUS_ACTIVE,
            'next_renewal_at' => $nextRenewal,
        ]);

        // Provision renewal fulfillment order in Shopify
        $shop = $subscription->shop;
        $shopifyOrderGid = $shopifyOrderService->createDepositOrder($shop, [
            'email' => $subscription->customer->email,
            'variant_id' => 1,
            'quantity' => 1,
            'total_cents' => (int) ($invoice['amount_paid'] ?? 0),
            'deposit_cents' => (int) ($invoice['amount_paid'] ?? 0),
            'currency' => strtoupper($invoice['currency'] ?? ($shop->currency ?? 'AUD')),
            'title' => "Ceramic Club Subscription Renewal — " . ($subscription->plan->name ?? 'Monthly Club'),
        ]);

        \App\Models\AuditLog::record(
            shopId: $subscription->shop_id,
            action: 'subscription.renewed',
            target: $subscription,
            meta: [
                'stripe_subscription_id' => $stripeSubId,
                'amount_cents' => $invoice['amount_paid'] ?? 0,
                'next_renewal_at' => $nextRenewal->toIso8601String(),
                'shopify_order_id' => $shopifyOrderGid,
            ]
        );

        Log::info("Processed subscription renewal for {$stripeSubId}, provisioned Shopify order: {$shopifyOrderGid}");
    }
}
