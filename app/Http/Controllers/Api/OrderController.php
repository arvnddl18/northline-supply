<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class OrderController extends Controller
{
    /**
     * List merchant orders with payment status, schedule details, and balance (US-05).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $status = $request->query('status');
        $queryParam = $request->query('query');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = $shop->orders()
            ->with(['customer', 'plan', 'paymentSchedules', 'payments', 'refunds'])
            ->latest();

        if (!empty($status)) {
            $query->where('payment_status', $status);
        }

        if (!empty($queryParam)) {
            $query->where(function ($q) use ($queryParam) {
                $q->where('shopify_order_id', 'like', "%{$queryParam}%")
                    ->orWhereHas('customer', function ($cq) use ($queryParam) {
                        $cq->where('email', 'like', "%{$queryParam}%");
                    });
            });
        }

        if (!empty($fromDate)) {
            $query->where('created_at', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $query->where('created_at', '<=', $toDate);
        }

        $orders = $query->paginate(20);

        $transformed = $orders->through(function (Order $order) {
            $schedule = $order->paymentSchedules->first();
            $balanceLink = null;
            if ($schedule && $schedule->status !== 'succeeded') {
                $balanceLink = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addDays(7));
            }

            return [
                'id' => $order->id,
                'shopify_order_id' => $order->shopify_order_id,
                'customer_email' => $order->customer?->email,
                'plan_name' => $order->plan?->name ?? 'Custom Deposit',
                'currency' => $order->currency,
                'total_amount' => $order->total_amount,
                'paid_amount' => $order->paid_amount,
                'total_refunded' => (int) $order->refunds->sum('amount'),
                'remaining_balance' => $order->remainingBalance(),
                'payment_status' => $order->payment_status,
                'status_label' => match ($order->payment_status) {
                    Order::STATUS_DEPOSIT_PAID => 'Deposit Paid — Balance Due',
                    Order::STATUS_PAID => 'Paid in Full',
                    Order::STATUS_PENDING_DEPOSIT => 'Pending Deposit',
                    Order::STATUS_FAILED => 'Payment Failed',
                    Order::STATUS_PARTIALLY_REFUNDED => 'Partially Refunded',
                    Order::STATUS_REFUNDED => 'Refunded in Full',
                    default => ucfirst(str_replace('_', ' ', $order->payment_status)),
                },
                'due_date' => $schedule && $schedule->due_at ? $schedule->due_at->toIso8601String() : null,
                'balance_link' => $balanceLink,
                'created_at' => $order->created_at->toIso8601String(),
            ];
        });

        return response()->json($transformed);
    }

    /**
     * View detailed order record.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $order = $shop->orders()
            ->with(['customer', 'plan', 'paymentSchedules', 'payments', 'emailLogs', 'refunds'])
            ->find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        $schedule = $order->paymentSchedules->first();
        $balanceLink = null;
        if ($schedule && $schedule->status !== 'succeeded') {
            $balanceLink = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addDays(7));
        }

        return response()->json([
            'order' => [
                'id' => $order->id,
                'shopify_order_id' => $order->shopify_order_id,
                'customer' => $order->customer,
                'plan' => $order->plan,
                'currency' => $order->currency,
                'total_amount' => $order->total_amount,
                'paid_amount' => $order->paid_amount,
                'total_refunded' => (int) $order->refunds->sum('amount'),
                'remaining_balance' => $order->remainingBalance(),
                'payment_status' => $order->payment_status,
                'balance_link' => $balanceLink,
                'schedules' => $order->paymentSchedules,
                'payments' => $order->payments,
                'refunds' => $order->refunds,
                'email_logs' => $order->emailLogs,
                'created_at' => $order->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Regenerate and return signed balance payment link for manual sharing (US-06).
     */
    public function paymentLink(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $order = $shop->orders()->with('paymentSchedules')->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        $schedule = $order->paymentSchedules->where('status', '!=', 'cancelled')->first();
        if (!$schedule) {
            return response()->json(['error' => 'No active payment schedule found for order.'], 404);
        }

        $expiration = $schedule->due_at ? $schedule->due_at->copy()->addDays(7) : now()->addDays(14);
        $link = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], $expiration);

        return response()->json([
            'order_id' => $order->id,
            'schedule_id' => $schedule->id,
            'amount' => $schedule->amount,
            'currency' => $order->currency,
            'payment_link' => $link,
            'expires_at' => $expiration->toIso8601String(),
        ]);
    }

    /**
     * Issue full or partial refund to Stripe and record in database & audit log (S3-T3, US-08).
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $order = $shop->orders()->with(['payments', 'refunds'])->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        $validated = $request->validate([
            'payment_id' => 'nullable|integer',
            'amount' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $amount = (int) $validated['amount'];
        $alreadyRefunded = (int) $order->refunds()->sum('refunds.amount');
        $maxRefundable = $order->paid_amount - $alreadyRefunded;

        if ($amount > $maxRefundable) {
            return response()->json([
                'error' => "Requested refund amount ({$amount}) exceeds remaining refundable paid amount ({$maxRefundable}).",
            ], 422);
        }

        // Find specific payment or latest succeeded payment to refund against
        $paymentQuery = $order->payments()->where('status', \App\Models\Payment::STATUS_SUCCEEDED);
        if (!empty($validated['payment_id'])) {
            $payment = $paymentQuery->where('id', (int) $validated['payment_id'])->first();
        } else {
            $payment = $paymentQuery->latest()->first();
        }

        if (!$payment) {
            return response()->json(['error' => 'No succeeded payment found for order to refund.'], 422);
        }

        $stripeKey = $shop->getStripeSecretKey();
        if (empty($stripeKey)) {
            return response()->json(['error' => 'Stripe secret key not configured for shop.'], 409);
        }

        // Execute Stripe Refund API call
        $stripeResponse = \Illuminate\Support\Facades\Http::withToken($stripeKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/refunds', [
                'payment_intent' => $payment->stripe_payment_intent_id,
                'amount' => $amount,
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'shopify_order_id' => (string) ($order->shopify_order_id ?? ''),
                ],
            ]);

        if ($stripeResponse->failed()) {
            $err = $stripeResponse->json('error.message') ?? 'Stripe refund request failed.';
            return response()->json(['error' => $err], 502);
        }

        $stripeRefund = $stripeResponse->json();
        $stripeRefundId = $stripeRefund['id'] ?? 're_'.uniqid();

        // shortcut: Shopify Admin refund API synced asynchronously; recorded in audit log for MVP (S3-T3)
        $shopifyRefundId = null;

        $refund = $payment->refunds()->create([
            'stripe_refund_id' => $stripeRefundId,
            'shopify_refund_id' => $shopifyRefundId,
            'amount' => $amount,
            'reason' => $validated['reason'] ?? 'Merchant initiated refund',
            'created_by' => $request->attributes->get('user_id'),
        ]);

        $newTotalRefunded = $alreadyRefunded + $amount;
        if ($newTotalRefunded >= $order->paid_amount) {
            $order->payment_status = Order::STATUS_REFUNDED;
            // ADR-006 rule: Cancel pending balance schedules when order is refunded
            $order->paymentSchedules()
                ->where('status', \App\Models\PaymentSchedule::STATUS_PENDING)
                ->update(['status' => \App\Models\PaymentSchedule::STATUS_CANCELLED]);
        } else {
            $order->payment_status = Order::STATUS_PARTIALLY_REFUNDED;
        }
        $order->save();

        \App\Models\AuditLog::record(
            shopId: $shop->id,
            action: 'refund.issued',
            target: $order,
            meta: [
                'refund_id' => $refund->id,
                'amount' => $amount,
                'stripe_refund_id' => $stripeRefundId,
                'reason' => $refund->reason,
            ],
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Refund processed successfully.',
            'refund' => $refund,
            'order' => [
                'id' => $order->id,
                'payment_status' => $order->payment_status,
                'paid_amount' => $order->paid_amount,
                'total_refunded' => $newTotalRefunded,
            ],
        ]);
    }
}
