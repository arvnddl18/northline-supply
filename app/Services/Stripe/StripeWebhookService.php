<?php

namespace App\Services\Stripe;

class StripeWebhookService
{
    /**
     * Verify Stripe webhook signature using native HMAC-SHA256 without third-party SDK bloat.
     */
    public function verifyHeader(string $payload, ?string $sigHeader, ?string $secret, int $tolerance = 300): bool
    {
        if (empty($sigHeader) || empty($secret)) {
            return false;
        }

        $items = explode(',', $sigHeader);
        $timestamp = null;
        $signatures = [];

        foreach ($items as $item) {
            $parts = explode('=', trim($item), 2);
            if (count($parts) !== 2) {
                continue;
            }

            if ($parts[0] === 't') {
                $timestamp = (int) $parts[1];
            } elseif ($parts[0] === 'v1') {
                $signatures[] = $parts[1];
            }
        }

        if (!$timestamp || empty($signatures)) {
            return false;
        }

        // Tolerance check (anti-replay attack)
        if ($tolerance > 0 && abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper to generate a valid Stripe-Signature header (useful for tests and webhook triggers).
     */
    public static function generateSignatureHeader(string $payload, string $secret, ?int $timestamp = null): string
    {
        $time = $timestamp ?? time();
        $signedPayload = "{$time}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return "t={$time},v1={$signature}";
    }
}
