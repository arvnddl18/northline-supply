<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class CardUpdateController extends Controller
{
    /**
     * Render the secure card update page via Stripe SetupIntent (S4-T5, US-20).
     */
    public function show(Request $request, PaymentSchedule $schedule): View
    {
        if (!$request->hasValidSignature()) {
            return view('customer.card_update_expired');
        }

        $order = $schedule->order;
        $shop = $order->shop;
        $customer = $order->customer;

        $secretKey = $shop->getStripeSecretKey();
        $publishableKey = $shop->getStripePublishableKey();

        // Create Stripe SetupIntent off-session
        $setupResponse = Http::withToken($secretKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/setup_intents', [
                'customer' => $customer->stripe_customer_id,
                'usage' => 'off_session',
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'schedule_id' => (string) $schedule->id,
                ],
            ]);

        $clientSecret = $setupResponse->json('client_secret');

        return view('customer.card_update', [
            'schedule' => $schedule,
            'order' => $order,
            'customer' => $customer,
            'publishableKey' => $publishableKey,
            'clientSecret' => $clientSecret,
        ]);
    }

    /**
     * Save the newly confirmed payment method token and re-queue schedule.
     */
    public function update(Request $request, PaymentSchedule $schedule): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['error' => 'Link has expired or is invalid.'], 403);
        }

        $validated = $request->validate([
            'payment_method_id' => 'required|string|starts_with:pm_',
        ]);

        $order = $schedule->order;
        $customer = $order->customer;

        // Update customer default payment method
        $customer->update([
            'default_payment_method' => $validated['payment_method_id'],
        ]);

        // Reset schedule to pending so the runner retries immediately
        if (in_array($schedule->status, [PaymentSchedule::STATUS_FAILED, 'requires_action', PaymentSchedule::STATUS_PENDING])) {
            $schedule->update([
                'status' => PaymentSchedule::STATUS_PENDING,
                'attempt_count' => 0,
                'next_retry_at' => now(),
            ]);
        }

        AuditLog::record(
            shopId: $order->shop_id,
            action: 'customer.card_updated',
            target: $customer,
            meta: [
                'payment_method_id' => $validated['payment_method_id'],
                'schedule_id' => $schedule->id,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method updated successfully. Next payment will process on schedule.',
        ]);
    }
}
