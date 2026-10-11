<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeWebhookJob;
use App\Models\WebhookEvent;
use App\Services\Stripe\StripeWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeWebhookService $webhookService
    ) {}

    /**
     * Ingest incoming Stripe webhook events with sub-second response and deduplication (S2-T2).
     */
    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET');

        // 1. Verify cryptographic signature (NFR-2)
        if (!$this->webhookService->verifyHeader($rawPayload, $sigHeader, $secret)) {
            Log::warning('Stripe webhook signature verification failed', [
                'ip' => $request->ip(),
                'signature' => $sigHeader ? substr($sigHeader, 0, 15) . '...' : null,
            ]);

            return response()->json(['error' => 'Invalid webhook signature.'], 400);
        }

        $payload = json_decode($rawPayload, true);
        if (!is_array($payload) || empty($payload['id'])) {
            return response()->json(['error' => 'Malformed webhook payload.'], 400);
        }

        $externalId = (string) $payload['id'];
        $topic = (string) ($payload['type'] ?? 'unknown');

        // 2. Atomic Deduplication via Unique external_id (NFR-6)
        $event = WebhookEvent::firstOrCreate(
            ['external_id' => $externalId],
            [
                'source' => 'stripe',
                'topic' => $topic,
                'payload' => $payload,
                'status' => WebhookEvent::STATUS_PENDING,
                'received_at' => now(),
            ]
        );

        if (!$event->wasRecentlyCreated) {
            Log::info("Ignoring duplicate Stripe webhook event [{$externalId}]");
            return response()->json([
                'status' => 'duplicate_ignored',
                'id' => $externalId,
            ], 200);
        }

        // 3. Dispatch async processing job (NFR-7, NFR-11)
        ProcessStripeWebhookJob::dispatch($event->id);

        return response()->json([
            'status' => 'queued',
            'id' => $externalId,
            'topic' => $topic,
        ], 200);
    }
}
