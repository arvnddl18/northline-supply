<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\Stripe\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeConnectionController extends Controller
{
    public function __construct(
        protected StripeConnectService $stripeService
    ) {}

    /**
     * Get the current Stripe connection status for the authenticated shop.
     */
    public function status(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        return response()->json($this->stripeService->getStatus($shop));
    }

    /**
     * Connect or update Stripe API keys for the authenticated shop.
     */
    public function connect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stripe_publishable_key' => ['required', 'string'],
            'stripe_secret_key' => ['required', 'string'],
        ]);

        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $result = $this->stripeService->connect(
            $shop,
            $validated['stripe_publishable_key'],
            $validated['stripe_secret_key']
        );

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error'],
            ], 422);
        }

        \App\Models\AuditLog::record(
            shopId: $shop->id,
            action: 'stripe.connected',
            target: $shop,
            meta: [
                'stripe_account_id' => $shop->stripe_account_id,
            ],
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Stripe credentials successfully verified and connected.',
            'status' => $this->stripeService->getStatus($shop),
        ]);
    }

    /**
     * Disconnect custom Stripe keys from the authenticated shop.
     */
    public function disconnect(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $result = $this->stripeService->disconnect($shop);

        \App\Models\AuditLog::record(
            shopId: $shop->id,
            action: 'stripe.disconnected',
            target: $shop,
            meta: [],
            userId: $request->attributes->get('user_id')
        );

        return response()->json([
            'message' => 'Stripe account disconnected.',
            'status' => $this->stripeService->getStatus($shop),
        ]);
    }
}
