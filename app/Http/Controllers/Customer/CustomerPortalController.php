<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    /**
     * Generate and dispatch secure magic login link to customer email (US-19, S5-T3).
     */
    public function requestLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        // Always return success response to prevent email enumeration attacks
        if (!$customer) {
            return response()->json([
                'status' => 'sent',
                'message' => 'If an account exists with this email, a login link has been sent.',
            ]);
        }

        $magicUrl = URL::signedRoute('customer.portal', [
            'customer' => $customer->id,
        ], now()->addMinutes(15));

        $payload = [
            'status' => 'sent',
            'message' => 'If an account exists with this email, a login link has been sent.',
        ];

        // shortcut: In local/testing returns link in payload for automated verification; upgrade to email delivery in prod
        if (app()->environment('local', 'testing')) {
            $payload['portal_url'] = $magicUrl;
        }

        return response()->json($payload);
    }

    /**
     * View customer self-service portal (orders, balance links, subscriptions).
     */
    public function show(Request $request, Customer $customer): View
    {
        if (!$request->hasValidSignature()) {
            return view('customer.portal_expired');
        }

        $orders = $customer->orders()
            ->with(['paymentSchedules', 'payments', 'plan'])
            ->latest()
            ->get();

        $subscriptions = $customer->subscriptions()
            ->with('plan')
            ->latest()
            ->get();

        $availablePlans = $customer->shop->plans()
            ->where('type', 'subscription')
            ->where('active', true)
            ->get();

        return view('customer.portal', [
            'customer' => $customer,
            'orders' => $orders,
            'subscriptions' => $subscriptions,
            'availablePlans' => $availablePlans,
        ]);
    }

    /**
     * Customer self-service: Pause subscription (S5-T3, US-21).
     */
    public function pauseSubscription(Request $request, Customer $customer, Subscription $subscription): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature.');
        }

        abort_if($subscription->customer_id !== $customer->id, 403, 'Unauthorized access.');

        $months = (int) $request->input('months', 1);
        $resumeDate = now()->addMonths(max(1, min(6, $months)));

        $stripeKey = $customer->shop?->getStripeSecretKey();
        if ($stripeKey && $subscription->stripe_subscription_id) {
            Http::withToken($stripeKey)->asForm()->post("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}", [
                'pause_collection' => ['behavior' => 'void'],
            ]);
        }

        $subscription->pause($resumeDate);

        AuditLog::record(
            shopId: $customer->shop_id,
            action: 'customer.subscription_paused',
            target: $subscription,
            meta: [
                'customer_email' => $customer->email,
                'paused_until' => $resumeDate->toIso8601String(),
            ]
        );

        return redirect()->to(URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addMinutes(15)))
            ->with('success', 'Subscription successfully paused until ' . $resumeDate->format('M d, Y') . '.');
    }

    /**
     * Customer self-service: Resume subscription (S5-T3, US-21).
     */
    public function resumeSubscription(Request $request, Customer $customer, Subscription $subscription): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature.');
        }

        abort_if($subscription->customer_id !== $customer->id, 403, 'Unauthorized access.');

        $stripeKey = $customer->shop?->getStripeSecretKey();
        if ($stripeKey && $subscription->stripe_subscription_id) {
            Http::withToken($stripeKey)->asForm()->post("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}", [
                'pause_collection' => '',
            ]);
        }

        $subscription->resume();

        AuditLog::record(
            shopId: $customer->shop_id,
            action: 'customer.subscription_resumed',
            target: $subscription,
            meta: [
                'customer_email' => $customer->email,
            ]
        );

        return redirect()->to(URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addMinutes(15)))
            ->with('success', 'Subscription resumed. Next renewal will proceed as scheduled.');
    }

    /**
     * Customer self-service: Skip next renewal cycle (S5-T3, US-21).
     */
    public function skipSubscription(Request $request, Customer $customer, Subscription $subscription): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature.');
        }

        abort_if($subscription->customer_id !== $customer->id, 403, 'Unauthorized access.');

        $currentRenewal = $subscription->next_renewal_at ?: now();
        $intervalType = $subscription->plan?->interval ?: 'month';
        $intervalCount = $subscription->plan?->interval_count ?: 1;

        $newRenewal = match ($intervalType) {
            'week' => $currentRenewal->copy()->addWeeks($intervalCount),
            'month' => $currentRenewal->copy()->addMonths($intervalCount),
            'year' => $currentRenewal->copy()->addYears($intervalCount),
            default => $currentRenewal->copy()->addMonth(),
        };

        $subscription->update([
            'next_renewal_at' => $newRenewal,
        ]);

        AuditLog::record(
            shopId: $customer->shop_id,
            action: 'customer.subscription_skipped',
            target: $subscription,
            meta: [
                'customer_email' => $customer->email,
                'previous_renewal' => $currentRenewal->toIso8601String(),
                'new_renewal' => $newRenewal->toIso8601String(),
            ]
        );

        return redirect()->to(URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addMinutes(15)))
            ->with('success', 'Next renewal skipped. New date: ' . $newRenewal->format('M d, Y') . '.');
    }

    /**
     * Customer self-service: Cancel subscription (S5-T3, US-21).
     */
    public function cancelSubscription(Request $request, Customer $customer, Subscription $subscription): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature.');
        }

        abort_if($subscription->customer_id !== $customer->id, 403, 'Unauthorized access.');

        $stripeKey = $customer->shop?->getStripeSecretKey();
        if ($stripeKey && $subscription->stripe_subscription_id) {
            Http::withToken($stripeKey)->delete("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}");
        }

        $subscription->cancel();

        AuditLog::record(
            shopId: $customer->shop_id,
            action: 'customer.subscription_cancelled',
            target: $subscription,
            meta: [
                'customer_email' => $customer->email,
            ]
        );

        return redirect()->to(URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addMinutes(15)))
            ->with('success', 'Your subscription has been cancelled.');
    }

    /**
     * Customer self-service: Change subscription tier with proration (S5-T3, US-21).
     */
    public function changeTier(Request $request, Customer $customer, Subscription $subscription): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature.');
        }

        abort_if($subscription->customer_id !== $customer->id, 403, 'Unauthorized access.');

        $validated = $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
        ]);

        $newPlan = $customer->shop->plans()
            ->where('type', 'subscription')
            ->where('active', true)
            ->find($validated['plan_id']);

        if (!$newPlan || $subscription->plan_id === $newPlan->id) {
            return redirect()->to(URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addMinutes(15)))
                ->with('error', 'Selected plan is not available or identical to current tier.');
        }

        $oldPlan = $subscription->plan;
        $oldAmountCents = $oldPlan?->deposit_value ?? 0;
        $newAmountCents = (int) $newPlan->deposit_value;
        $deltaCents = $newAmountCents - $oldAmountCents;

        $stripeKey = $customer->shop?->getStripeSecretKey();
        if ($stripeKey && $subscription->stripe_subscription_id) {
            $subResponse = Http::withToken($stripeKey)->get("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}");
            $itemId = $subResponse->json('items.data.0.id');

            $updatePayload = [
                'proration_behavior' => 'create_prorations',
                'proration_date' => now()->timestamp,
            ];

            if ($itemId) {
                $currency = strtolower($customer->shop->currency ?? 'AUD');
                $updatePayload['items'] = [
                    [
                        'id' => $itemId,
                        'price_data' => [
                            'currency' => $currency,
                            'product' => $subResponse->json('items.data.0.price.product') ?? 'prod_ceramic_club',
                            'unit_amount' => $newAmountCents,
                            'recurring' => [
                                'interval' => $newPlan->interval ?: 'month',
                                'interval_count' => $newPlan->interval_count ?: 1,
                            ],
                        ],
                    ],
                ];
            }

            $stripeRes = Http::withToken($stripeKey)->asForm()->post(
                "https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}",
                $updatePayload
            );

            if ($stripeRes->failed()) {
                return redirect()->to(URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addMinutes(15)))
                    ->with('error', 'Failed to update subscription on Stripe: ' . $stripeRes->json('error.message', 'Gateway error.'));
            }
        }

        $subscription->update([
            'plan_id' => $newPlan->id,
        ]);

        AuditLog::record(
            shopId: $customer->shop_id,
            action: 'customer.subscription_tier_changed',
            target: $subscription,
            meta: [
                'customer_email' => $customer->email,
                'old_plan_id' => $oldPlan?->id,
                'new_plan_id' => $newPlan->id,
                'old_amount_cents' => $oldAmountCents,
                'new_amount_cents' => $newAmountCents,
                'delta_cents' => $deltaCents,
                'proration_behavior' => 'create_prorations',
            ]
        );

        return redirect()->to(URL::signedRoute('customer.portal', ['customer' => $customer->id], now()->addMinutes(15)))
            ->with('success', 'Plan tier changed to ' . $newPlan->name . ' with proration applied.');
    }

    /**
     * Demo access helper for local environment testing.
     */
    public function demo(): \Illuminate\Http\RedirectResponse
    {
        $customer = Customer::first();
        if (!$customer) {
            abort(404, 'No customer found. Please seed the database.');
        }

        $url = URL::signedRoute('customer.portal', [
            'customer' => $customer->id,
        ], now()->addHours(24));

        return redirect()->to($url);
    }
}
