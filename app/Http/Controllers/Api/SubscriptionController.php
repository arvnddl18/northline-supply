<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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
}
