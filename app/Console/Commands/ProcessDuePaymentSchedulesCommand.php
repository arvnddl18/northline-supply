<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Services\Email\EmailService;
use App\Services\Shopify\ShopifyOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ProcessDuePaymentSchedulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-due-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge due payment schedules off-session using customer saved payment methods (S4-T3, S4-T4)';

    public function __construct(
        protected ShopifyOrderService $shopifyOrderService,
        protected EmailService $emailService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $this->info("Scanning for due payment schedules at {$now->toIso8601String()}...");

        $dueSchedules = PaymentSchedule::query()
            ->where('status', PaymentSchedule::STATUS_PENDING)
            ->where('due_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', $now);
            })
            ->where('attempt_count', '<', 3)
            ->with(['order.customer', 'order.shop'])
            ->get();

        Log::info("ProcessDueSchedules: found {$dueSchedules->count()} schedules");

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        foreach ($dueSchedules as $schedule) {
            $processedCount++;

            try {
                // Use transaction with pessimistic row lock to prevent double-charging (ADR-007)
                DB::transaction(function () use ($schedule, &$successCount, &$failedCount) {
                    /** @var PaymentSchedule $lockedSchedule */
                    $lockedSchedule = PaymentSchedule::where('id', $schedule->id)->lockForUpdate()->first();

                    if (!$lockedSchedule || $lockedSchedule->status !== PaymentSchedule::STATUS_PENDING) {
                        return;
                    }

                    $order = $lockedSchedule->order;
                    $shop = $order->shop;
                    $customer = $order->customer;

                    $stripeSecretKey = $shop->getStripeSecretKey();
                    if (empty($stripeSecretKey) || empty($customer?->stripe_customer_id)) {
                        Log::warning("Skipping schedule #{$lockedSchedule->id}: Missing Stripe credentials or customer ID.");
                        return;
                    }

                    $paymentMethodId = $customer->default_payment_method;
                    $idempotencyKey = "sched_{$lockedSchedule->id}_att_{$lockedSchedule->attempt_count}_" . now()->format('Ymd');

                    $payload = [
                        'amount' => $lockedSchedule->amount,
                        'currency' => strtolower($order->currency),
                        'customer' => $customer->stripe_customer_id,
                        'off_session' => 'true',
                        'confirm' => 'true',
                        'metadata' => [
                            'order_id' => (string) $order->id,
                            'schedule_id' => (string) $lockedSchedule->id,
                            'shopify_order_id' => (string) ($order->shopify_order_id ?? ''),
                        ],
                    ];

                    if (!empty($paymentMethodId)) {
                        $payload['payment_method'] = $paymentMethodId;
                    }

                    $response = Http::withToken($stripeSecretKey)
                        ->withHeaders(['Idempotency-Key' => $idempotencyKey])
                        ->asForm()
                        ->post('https://api.stripe.com/v1/payment_intents', $payload);

                    $responseData = $response->json();
                    $piStatus = $responseData['status'] ?? null;
                    $paymentIntentId = $responseData['id'] ?? 'pi_off_session_' . uniqid();

                    Log::info("Stripe PI response:", ['status_code' => $response->status(), 'piStatus' => $piStatus, 'data' => $responseData]);

                    if ($response->successful() && $piStatus === 'succeeded') {
                        // 1. Success Flow
                        $lockedSchedule->update([
                            'status' => PaymentSchedule::STATUS_SUCCEEDED,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'next_retry_at' => null,
                        ]);

                        Payment::create([
                            'order_id' => $order->id,
                            'schedule_id' => $lockedSchedule->id,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'amount' => $lockedSchedule->amount,
                            'status' => Payment::STATUS_SUCCEEDED,
                        ]);

                        $order->paid_amount += $lockedSchedule->amount;

                        // If all schedules succeeded or balance fully paid
                        $allSucceeded = $order->paymentSchedules()->where('status', '!=', PaymentSchedule::STATUS_SUCCEEDED)->count() === 0;
                        if ($allSucceeded || $order->remainingBalance() === 0) {
                            $order->payment_status = Order::STATUS_PAID;
                            $this->shopifyOrderService->syncPaymentStatus($order, Order::STATUS_PAID);
                        }
                        $order->save();

                        AuditLog::record(
                            shopId: $shop->id,
                            action: 'installment.charge_succeeded',
                            target: $lockedSchedule,
                            meta: [
                                'amount' => $lockedSchedule->amount,
                                'stripe_payment_intent_id' => $paymentIntentId,
                            ]
                        );

                        $this->emailService->sendBalancePaidReceipt($order);
                        $successCount++;
                    } elseif ($piStatus === 'requires_action') {
                        // 2. 3D Secure / SCA Required Flow
                        $lockedSchedule->update([
                            'status' => 'requires_action',
                            'stripe_payment_intent_id' => $paymentIntentId,
                        ]);

                        $link = URL::signedRoute('pay.balance', ['schedule' => $lockedSchedule->id], now()->addDays(7));
                        $this->emailService->sendBalanceReminder($lockedSchedule, $link);
                        $failedCount++;
                    } else {
                        // 3. Decline / Retry Flow (S4-T4)
                        $lockedSchedule->attempt_count += 1;
                        $failureCode = $responseData['error']['code'] ?? 'card_declined';

                        // Progressive retry schedule: Attempt 1 -> Day 1, Attempt 2 -> Day 3, Attempt 3 -> Day 5
                        $nextRetry = match ($lockedSchedule->attempt_count) {
                            1 => now()->addDays(1),
                            2 => now()->addDays(2),
                            default => null,
                        };

                        $lockedSchedule->next_retry_at = $nextRetry;

                        if ($lockedSchedule->attempt_count >= 3) {
                            // Terminal failure after 3 attempts: flag order for merchant attention, no automatic cancel (US-11)
                            $lockedSchedule->status = PaymentSchedule::STATUS_FAILED;
                            $order->payment_status = Order::STATUS_FAILED;
                            $order->save();

                            // Add Shopify tag installment-failed
                            $this->shopifyOrderService->addOrderTag($order, 'installment-failed');

                            AuditLog::record(
                                shopId: $shop->id,
                                action: 'installment.terminal_failed',
                                target: $lockedSchedule,
                                meta: [
                                    'attempts' => $lockedSchedule->attempt_count,
                                    'failure_code' => $failureCode,
                                ]
                            );
                        }

                        $lockedSchedule->save();

                        Payment::create([
                            'order_id' => $order->id,
                            'schedule_id' => $lockedSchedule->id,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'amount' => $lockedSchedule->amount,
                            'status' => Payment::STATUS_FAILED,
                            'failure_code' => $failureCode,
                        ]);

                        $link = URL::signedRoute('pay.balance', ['schedule' => $lockedSchedule->id], now()->addDays(7));
                        $this->emailService->sendBalanceReminder($lockedSchedule, $link);
                        $failedCount++;
                    }
                });
            } catch (\Throwable $e) {
                Log::error("Failed processing schedule #{$schedule->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $failedCount++;
            }
        }

        $this->info("Completed processing due schedules. Total: {$processedCount}, Succeeded: {$successCount}, Retrying/Failed: {$failedCount}");
        return 0;
    }
}
