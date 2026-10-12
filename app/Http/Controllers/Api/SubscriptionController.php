<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    /**
     * List shop recurring subscriptions (US-12, S5-T1).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $subscriptions = $shop->subscriptions()
            ->with(['customer', 'plan'])
            ->latest()
            ->paginate(20);

        return response()->json($subscriptions);
    }

    /**
     * View subscription record.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $subscription = $shop->subscriptions()->with(['customer', 'plan'])->find($id);
        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found.'], 404);
        }

        return response()->json($subscription);
    }

    /**
     * Pause subscription (S5-T2, US-21).
     */
    public function pause(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $subscription = $shop->subscriptions()->find($id);
        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found.'], 404);
        }

        $validated = $request->validate([
            'resume_date' => 'nullable|date|after:today',
        ]);

        $resumeDate = !empty($validated['resume_date']) 
            ? Carbon::parse($validated['resume_date']) 
            : now()->addMonth();

        // Pause collection on Stripe subscription
        $stripeKey = $shop->getStripeSecretKey();
        if ($stripeKey) {
            Http::withToken($stripeKey)->asForm()->post("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}", [
                'pause_collection' => ['behavior' => 'void'],
            ]);
        }

        $subscription->pause($resumeDate);

        AuditLog::record(
            shopId: $shop->id,
            action: 'subscription.paused',
            target: $subscription,
            meta: [
                'paused_until' => $resumeDate->toIso8601String(),
            ],
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Subscription paused successfully.',
            'subscription' => $subscription,
        ]);
    }

    /**
     * Resume a paused subscription (S5-T2, US-21).
     */
    public function resume(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $subscription = $shop->subscriptions()->find($id);
        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found.'], 404);
        }

        $stripeKey = $shop->getStripeSecretKey();
        if ($stripeKey) {
            Http::withToken($stripeKey)->asForm()->post("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}", [
                'pause_collection' => '',
            ]);
        }

        $subscription->resume();

        AuditLog::record(
            shopId: $shop->id,
            action: 'subscription.resumed',
            target: $subscription,
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Subscription resumed successfully.',
            'subscription' => $subscription,
        ]);
    }

    /**
     * Skip next renewal billing cycle (S5-T2, US-21).
     */
    public function skip(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $subscription = $shop->subscriptions()->with('plan')->find($id);
        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found.'], 404);
        }

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
            shopId: $shop->id,
            action: 'subscription.skipped_cycle',
            target: $subscription,
            meta: [
                'previous_renewal' => $currentRenewal->toIso8601String(),
                'new_renewal' => $newRenewal->toIso8601String(),
            ],
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Skipped next subscription billing cycle.',
            'subscription' => $subscription,
        ]);
    }

    /**
     * Cancel recurring subscription (S5-T2, US-21).
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $subscription = $shop->subscriptions()->find($id);
        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found.'], 404);
        }

        $stripeKey = $shop->getStripeSecretKey();
        if ($stripeKey) {
            Http::withToken($stripeKey)->delete("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}");
        }

        $subscription->cancel();

        AuditLog::record(
            shopId: $shop->id,
            action: 'subscription.cancelled',
            target: $subscription,
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Subscription cancelled successfully.',
            'subscription' => $subscription,
        ]);
    }

    /**
     * Change subscription plan tier with real-time Stripe proration (S5-T2, US-21).
     */
    public function changeTier(Request $request, int $id): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $subscription = $shop->subscriptions()->with('plan')->find($id);
        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found.'], 404);
        }

        $validated = $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
        ]);

        $newPlan = $shop->plans()->where('type', 'subscription')->where('active', true)->find($validated['plan_id']);
        if (!$newPlan) {
            return response()->json(['error' => 'Invalid or inactive subscription plan.'], 422);
        }

        if ($subscription->plan_id === $newPlan->id) {
            return response()->json(['error' => 'Subscription is already on this plan.'], 422);
        }

        $oldPlan = $subscription->plan;
        $oldAmountCents = $oldPlan?->deposit_value ?? 0;
        $newAmountCents = (int) $newPlan->deposit_value;
        $deltaCents = $newAmountCents - $oldAmountCents;

        $stripeKey = $shop->getStripeSecretKey();
        if ($stripeKey && $subscription->stripe_subscription_id) {
            $subResponse = Http::withToken($stripeKey)->get("https://api.stripe.com/v1/subscriptions/{$subscription->stripe_subscription_id}");
            $itemId = $subResponse->json('items.data.0.id');

            $updatePayload = [
                'proration_behavior' => 'create_prorations',
                'proration_date' => now()->timestamp,
            ];

            if ($itemId) {
                $currency = strtolower($shop->currency ?? 'AUD');
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
                return response()->json([
                    'error' => 'Failed to update subscription on Stripe: ' . $stripeRes->json('error.message', 'Gateway error.'),
                ], 502);
            }
        }

        $subscription->update([
            'plan_id' => $newPlan->id,
        ]);

        AuditLog::record(
            shopId: $shop->id,
            action: 'subscription.tier_changed',
            target: $subscription,
            meta: [
                'old_plan_id' => $oldPlan?->id,
                'new_plan_id' => $newPlan->id,
                'old_amount_cents' => $oldAmountCents,
                'new_amount_cents' => $newAmountCents,
                'delta_cents' => $deltaCents,
                'proration_behavior' => 'create_prorations',
            ],
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Subscription plan tier changed successfully with proration.',
            'subscription' => $subscription->fresh()->load('plan'),
            'delta_cents' => $deltaCents,
        ]);
    }
}
