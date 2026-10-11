<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Shop;
use App\Services\Payment\DepositCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AppProxyController extends Controller
{
    public function __construct(
        protected DepositCalculationService $depositService
    ) {}

    /**
     * Resolve eligible deposit plan and calculate breakdown for a storefront product.
     */
    public function planInfo(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $productId = (string) $request->query('product_id');
        $rawPrice = $request->query('price', 0);
        $currency = strtoupper((string) ($request->query('currency') ?: ($shop->currency ?? 'AUD')));

        // Standardize price into integer cents (Shopify liquid passes either cents or float dollars)
        $totalCents = is_numeric($rawPrice) ? (int) $rawPrice : 0;
        if (str_contains((string) $rawPrice, '.')) {
            $totalCents = (int) round(((float) $rawPrice) * 100);
        }

        if ($totalCents <= 0) {
            return response()->json([
                'eligible' => false,
                'error' => 'Price must be greater than zero.',
            ], 400);
        }

        // Parse collection IDs
        $collectionInput = $request->query('collection_ids', '');
        $collectionIds = is_array($collectionInput)
            ? $collectionInput
            : array_filter(array_map('trim', explode(',', (string) $collectionInput)));

        // Resolve matching plan via priority hierarchy
        $plan = $this->resolvePlan($shop, $productId, $collectionIds);

        if (!$plan) {
            return response()->json([
                'eligible' => false,
                'message' => 'Product is not eligible for pre-order deposits.',
            ]);
        }

        try {
            $calculation = $this->depositService->calculate(
                $totalCents,
                $plan->deposit_type,
                $plan->deposit_value,
                $currency
            );

            return response()->json([
                'eligible' => true,
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'type' => $plan->type,
                    'deposit_type' => $plan->deposit_type,
                    'deposit_value' => $plan->deposit_value,
                    'lead_time' => '3–6 weeks handmade lead time',
                ],
                'calculation' => $calculation,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'eligible' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create a Stripe Checkout session for the deposit and redirect (ADR-002 Option B).
     * shortcut: price comes from the signed proxy query (HMAC-protected), not re-fetched from Shopify Admin API; upgrade when order creation (S2) lands.
     */
    public function startCheckout(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $data = $request->validate([
            'product_id' => 'required|string|max:100',
            'variant_id' => 'required|string|max:100',
            'plan_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:99',
            'price' => 'required|integer|min:1',
            'currency' => 'nullable|string|size:3',
            'title' => 'nullable|string|max:200',
        ]);

        $plan = $shop->plans()->active()->find($data['plan_id']);
        $secret = $shop->getStripeSecretKey();
        if (!$plan || !$secret) {
            return response()->json(['error' => $plan ? 'Stripe is not connected.' : 'Plan not found.'], $plan ? 409 : 404);
        }

        $qty = (int) ($data['quantity'] ?? 1);
        $currency = strtoupper($data['currency'] ?? ($shop->currency ?? 'AUD'));
        $totalCents = (int) $data['price'] * $qty;

        try {
            $calc = $this->depositService->calculate($totalCents, $plan->deposit_type, $plan->deposit_value, $currency);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $name = $data['title'] ?? 'Pre-order item';
        $base = 'https://'.$shop->shopify_domain;
        $response = Http::withBasicAuth($secret, '')->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
            'mode' => 'payment',
            'payment_intent_data' => ['setup_future_usage' => 'off_session'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => $calc['deposit_cents'],
                    'product_data' => ['name' => "Pre-order Deposit: {$name}"],
                ],
            ]],
            'metadata' => [
                'shop_id' => $shop->id,
                'plan_id' => $plan->id,
                'product_id' => $data['product_id'],
                'variant_id' => $data['variant_id'],
                'quantity' => $qty,
                'total_cents' => $totalCents,
                'deposit_cents' => $calc['deposit_cents'],
                'balance_cents' => $calc['balance_cents'],
                'currency' => $currency,
            ],
            'success_url' => $base.'/pages/deposit-confirmed?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $base.'/products',
        ]);

        if ($response->failed() || empty($response['url'])) {
            return response()->json(['error' => 'Unable to start checkout.'], 502);
        }

        return $request->expectsJson() || $request->query('format') === 'json'
            ? response()->json(['redirect_url' => $response['url']])
            : redirect()->away($response['url']);
    }

    /**
     * Resolve the most specific active plan for a product according to hierarchy:
     * 1. Product-specific target
     * 2. Collection-specific target
     * 3. Shop-wide default plan (plan with no targets)
     */
    protected function resolvePlan(Shop $shop, string $productId, array $collectionIds): ?Plan
    {
        $activePlans = $shop->plans()->active()->with('targets')->get();

        if ($activePlans->isEmpty()) {
            return null;
        }

        $normalizedProductIds = array_filter([
            $productId,
            str_replace('gid://shopify/Product/', '', $productId),
            !str_starts_with($productId, 'gid://shopify/Product/') ? "gid://shopify/Product/{$productId}" : null,
        ]);

        // 1. Check Product-specific target match
        foreach ($activePlans as $plan) {
            foreach ($plan->targets as $target) {
                if ($target->shopify_product_id && in_array($target->shopify_product_id, $normalizedProductIds, true)) {
                    return $plan;
                }
            }
        }

        // 2. Check Collection-specific target match
        if (!empty($collectionIds)) {
            $normalizedCollectionIds = [];
            foreach ($collectionIds as $colId) {
                $normalizedCollectionIds[] = (string) $colId;
                $normalizedCollectionIds[] = str_replace('gid://shopify/Collection/', '', (string) $colId);
                if (!str_starts_with((string) $colId, 'gid://shopify/Collection/')) {
                    $normalizedCollectionIds[] = "gid://shopify/Collection/{$colId}";
                }
            }

            foreach ($activePlans as $plan) {
                foreach ($plan->targets as $target) {
                    if ($target->shopify_collection_id && in_array($target->shopify_collection_id, $normalizedCollectionIds, true)) {
                        return $plan;
                    }
                }
            }
        }

        // 3. Fallback: Shop-wide default plan (active plan with zero specific targets)
        foreach ($activePlans as $plan) {
            if ($plan->targets->isEmpty()) {
                return $plan;
            }
        }

        return null;
    }
}
