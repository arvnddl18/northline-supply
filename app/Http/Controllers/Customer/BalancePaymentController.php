<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class BalancePaymentController extends Controller
{
    /**
     * Display the balance payment link landing page (S2-T5).
     */
    public function show(Request $request, int $scheduleId): View
    {
        // 1. Signature validation (NFR-2, US-18)
        if (!$request->hasValidSignature()) {
            return view('customer.balance_payment', [
                'error' => 'This balance payment link has expired or is invalid. Please contact Northwind Ceramics to receive a new payment link.',
                'isPaid' => false,
            ]);
        }

        $schedule = PaymentSchedule::with(['order.shop', 'order.customer'])->find($scheduleId);
        if (!$schedule) {
            abort(404, 'Schedule not found.');
        }

        $order = $schedule->order;
        $isPaid = $schedule->status === PaymentSchedule::STATUS_SUCCEEDED || $order->isSettled();

        $currency = $order->currency;
        $totalFormatted = number_format($order->total_amount / 100, 2);
        $depositFormatted = number_format($order->paid_amount / 100, 2);
        $balanceFormatted = number_format($schedule->amount / 100, 2);
        $dueDate = $schedule->due_at ? $schedule->due_at->format('M j, Y') : 'on completion';
        $orderNumber = $order->shopify_order_id ? '#' . str_replace('gid://shopify/Order/', '', $order->shopify_order_id) : '#' . $order->id;

        // Generate signed URL for checkout POST action, preserving current query expiration
        $checkoutUrl = URL::signedRoute('pay.balance.checkout', ['schedule' => $schedule->id], now()->addHours(2));

        return view('customer.balance_payment', [
            'schedule' => $schedule,
            'order' => $order,
            'isPaid' => $isPaid,
            'error' => null,
            'currency' => $currency,
            'totalFormatted' => $totalFormatted,
            'depositFormatted' => $depositFormatted,
            'balanceFormatted' => $balanceFormatted,
            'dueDate' => $dueDate,
            'orderNumber' => $orderNumber,
            'checkoutUrl' => $checkoutUrl,
        ]);
    }

    /**
     * Initiate Stripe Checkout session for balance settlement (S2-T5).
     */
    public function checkout(Request $request, int $scheduleId): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['error' => 'Payment link has expired.'], 403);
        }

        $schedule = PaymentSchedule::with('order.shop')->find($scheduleId);
        if (!$schedule) {
            return response()->json(['error' => 'Payment schedule not found.'], 404);
        }

        $order = $schedule->order;
        $shop = $order->shop;

        if ($schedule->status === PaymentSchedule::STATUS_SUCCEEDED || $order->isSettled()) {
            return redirect()->to(URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addDays(7)));
        }

        $secret = $shop->getStripeSecretKey();
        if (!$secret) {
            return response()->json(['error' => 'Stripe is not configured for this shop.'], 409);
        }

        $currency = strtolower($order->currency);
        $orderRef = $order->shopify_order_id ? str_replace('gid://shopify/Order/', '', $order->shopify_order_id) : (string) $order->id;
        $domain = $shop->shopify_domain;

        $cancelUrl = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addDays(7));
        $successUrl = "https://{$domain}/pages/balance-paid?session_id={CHECKOUT_SESSION_ID}&order_id={$order->id}";

        try {
            $response = Http::withBasicAuth($secret, '')->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $schedule->amount,
                        'product_data' => [
                            'name' => "Pre-order Final Balance: Order #{$orderRef}",
                        ],
                    ],
                ]],
                'metadata' => [
                    'type' => 'balance_settlement',
                    'shop_id' => $shop->id,
                    'order_id' => $order->id,
                    'schedule_id' => $schedule->id,
                    'amount_total' => $schedule->amount,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            if ($response->failed() || empty($response['url'])) {
                Log::error('Stripe balance session creation failed: ' . $response->body());
                return response()->json(['error' => 'Unable to initiate Stripe checkout.'], 502);
            }

            return $request->expectsJson()
                ? response()->json(['redirect_url' => $response['url']])
                : redirect()->away($response['url']);
        } catch (\Throwable $e) {
            Log::error('Exception creating balance checkout session: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processor error.'], 500);
        }
    }
}
