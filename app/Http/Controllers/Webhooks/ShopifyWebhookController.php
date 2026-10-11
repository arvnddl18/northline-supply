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

        $topic = (string) $request->header('X-Shopify-Topic');
        $shopDomain = (string) $request->header('X-Shopify-Shop-Domain');
        $webhookId = (string) ($request->header('X-Shopify-Webhook-Id') ?: ('shopify_' . md5($rawPayload)));

        Log::info("Received Shopify webhook [{$topic}] for {$shopDomain} (id: {$webhookId})");

        // 2. Deduplication check via WebhookEvent
        $parsedJson = json_decode($rawPayload, true) ?: [];
        $event = \App\Models\WebhookEvent::firstOrCreate(
            ['external_id' => $webhookId],
            [
                'source' => 'shopify',
                'topic' => $topic,
                'payload' => $parsedJson,
                'status' => \App\Models\WebhookEvent::STATUS_PENDING,
                'received_at' => now(),
            ]
        );

        if (!$event->wasRecentlyCreated) {
            Log::info("Ignoring duplicate Shopify webhook [{$webhookId}]");
            return response()->json([
                'status' => 'duplicate_ignored',
                'id' => $webhookId,
            ], 200);
        }

        // 3. Route topics
        if ($topic === 'app/uninstalled') {
            $this->handleAppUninstalled($shopDomain);
        }

        $event->markProcessed();

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
