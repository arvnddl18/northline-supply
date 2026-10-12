<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOverviewController extends Controller
{
    /**
     * Return summary information for the authenticated merchant in the embedded admin.
     */
    public function show(Request $request): JsonResponse
    {
        $shop = $request->attributes->get('shop');

        $plansCount = $shop->plans()->count();
        $isStripeConnected = $shop->isStripeConnected();

        return response()->json([
            'shop' => [
                'id' => $shop->id,
                'shopify_domain' => $shop->shopify_domain,
                'currency' => $shop->currency ?? 'AUD',
                'installed_at' => $shop->installed_at?->toIso8601String(),
                'stripe_connected' => $isStripeConnected,
                'plans_count' => $plansCount,
            ],
            'checklist' => [
                'app_installed' => true,
                'stripe_connected' => $isStripeConnected,
                'plans_configured' => $plansCount > 0,
                'ready_for_storefront' => $isStripeConnected && $plansCount > 0,
            ],
            'user_role' => $request->attributes->get('user')?->role ?? 'owner',
            'version' => '1.0.0',
        ]);
    }
}
