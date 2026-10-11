<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\Shopify\ShopifyOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends Controller
{
    public function __construct(
        protected ShopifyOAuthService $oauthService
    ) {}

    /**
     * Handle incoming Shopify webhooks with cryptographic HMAC verification.
     */
    public function handle(Request $request): JsonResponse
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $rawPayload = $request->getContent();

        // 1. Verify Shopify HMAC signature (NFR-2)
        if (!$this->oauthService->verifyWebhookHmac($rawPayload, $hmacHeader)) {
            Log::warning('Shopify webhook HMAC verification failed', [
                'ip' => $request->ip(),
                'topic' => $request->header('X-Shopify-Topic'),
                'shop' => $request->header('X-Shopify-Shop-Domain'),
            ]);

            return response()->json(['error' => 'Unauthorized: Invalid webhook signature.'], 401);
        }

        $topic = $request->header('X-Shopify-Topic');
        $shopDomain = $request->header('X-Shopify-Shop-Domain');

        Log::info("Received Shopify webhook [{$topic}] for {$shopDomain}");

        // 2. Route topics
        if ($topic === 'app/uninstalled') {
            $this->handleAppUninstalled($shopDomain);
        }

        // Sub-second response as required by NFR-11
        return response()->json([
            'status' => 'handled',
            'topic' => $topic,
            'shop' => $shopDomain,
        ], 200);
    }

    /**
     * Handle app/uninstalled webhook: clear encrypted access token and mark uninstalled.
     */
    protected function handleAppUninstalled(?string $shopDomain): void
    {
        if (empty($shopDomain)) {
            return;
        }

        $shop = Shop::where('shopify_domain', $shopDomain)->first();

        if ($shop) {
            $shop->markUninstalled();
            Log::info("Shop [{$shopDomain}] marked uninstalled and access token cleared.");
        }
    }
}
