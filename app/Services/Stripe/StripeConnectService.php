<?php

namespace App\Services\Stripe;

use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeConnectService
{
    /**
     * Ping Stripe API to verify API keys and retrieve account details.
     *
     * @return array{success: bool, error?: string, account_id?: string, charges_enabled?: bool, payouts_enabled?: bool, default_currency?: string, test_mode?: bool, business_name?: ?string}
     */
    public function verifyCredentials(string $publishableKey, string $secretKey): array
    {
        $pubKey = trim($publishableKey);
        $secKey = trim($secretKey);

        // 1. Basic format sanity validation
        if (!preg_match('/^pk_(test|live)_[a-zA-Z0-9_]+$/', $pubKey)) {
            return [
                'success' => false,
                'error' => 'Invalid Stripe Publishable Key format. Must start with pk_test_ or pk_live_.',
            ];
        }

        if (!preg_match('/^(sk|rk)_(test|live)_[a-zA-Z0-9_]+$/', $secKey)) {
            return [
                'success' => false,
                'error' => 'Invalid Stripe Secret Key format. Must start with sk_test_, sk_live_, rk_test_, or rk_live_.',
            ];
        }

        // 2. Perform live ping via Stripe /v1/account
        try {
            $response = Http::withBasicAuth($secKey, '')
                ->timeout(5)
                ->get('https://api.stripe.com/v1/account');

            if ($response->failed()) {
                $errorMsg = $response->json('error.message') ?? 'Stripe authentication failed with status ' . $response->status();
                return [
                    'success' => false,
                    'error' => $errorMsg,
                ];
            }

            $account = $response->json();
            $isTest = str_starts_with($secKey, 'sk_test_') || str_starts_with($pubKey, 'pk_test_');

            return [
                'success' => true,
                'account_id' => $account['id'] ?? null,
                'charges_enabled' => (bool) ($account['charges_enabled'] ?? false),
                'payouts_enabled' => (bool) ($account['payouts_enabled'] ?? false),
                'default_currency' => strtoupper($account['default_currency'] ?? 'AUD'),
                'test_mode' => $isTest,
                'business_name' => $account['business_profile']['name'] ?? ($account['settings']['dashboard']['display_name'] ?? null),
            ];
        } catch (\Throwable $e) {
            Log::error('Stripe credential verification exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Unable to connect to Stripe API. Please verify network or key validity.',
            ];
        }
    }

    /**
     * Connect merchant shop to Stripe using validated credentials.
     */
    public function connect(Shop $shop, string $publishableKey, string $secretKey): array
    {
        $verification = $this->verifyCredentials($publishableKey, $secretKey);

        if (!$verification['success']) {
            return $verification;
        }

        $shop->update([
            'stripe_account_id' => $verification['account_id'],
            'stripe_publishable_key' => trim($publishableKey),
            'stripe_secret_key' => trim($secretKey),
        ]);

        return array_merge($verification, [
            'status' => 'connected',
            'publishable_key' => $this->maskKey($publishableKey),
        ]);
    }

    /**
     * Disconnect Stripe credentials from shop.
     */
    public function disconnect(Shop $shop): array
    {
        $shop->update([
            'stripe_account_id' => null,
            'stripe_publishable_key' => null,
            'stripe_secret_key' => null,
        ]);

        return [
            'success' => true,
            'status' => 'disconnected',
            'connected' => false,
        ];
    }

    /**
     * Get current Stripe connection status for shop (with config fallback).
     */
    public function getStatus(Shop $shop): array
    {
        $pubKey = $shop->getStripePublishableKey();
        $secKey = $shop->getStripeSecretKey();
        $hasCustomKeys = !empty($shop->stripe_publishable_key) && !empty($shop->stripe_secret_key);
        $isConnected = !empty($secKey) && !empty($pubKey);

        $testMode = $shop->isStripeTestMode();

        return [
            'connected' => $isConnected,
            'stripe_account_id' => $shop->stripe_account_id,
            'test_mode' => $testMode,
            'has_custom_keys' => $hasCustomKeys,
            'publishable_key' => $pubKey ? $this->maskKey($pubKey) : null,
            'has_secret_key' => !empty($secKey),
            'currency' => $shop->currency ?? 'AUD',
        ];
    }

    /**
     * Mask API keys for secure UI representation (e.g. pk_test_...3a2b).
     */
    public function maskKey(string $key): string
    {
        $trimmed = trim($key);
        if (strlen($trimmed) <= 12) {
            return '********';
        }
        $prefix = substr($trimmed, 0, 8);
        $suffix = substr($trimmed, -4);
        return "{$prefix}..." . $suffix;
    }
}
