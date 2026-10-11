<?php

namespace App\Services\Shopify;

use Illuminate\Support\Facades\Http;

class ShopifyOAuthService
{
    /**
     * Validate if the given domain is a valid RFC-compliant .myshopify.com domain.
     */
    public function isValidShopDomain(?string $shop): bool
    {
        if (empty($shop)) {
            return false;
        }

        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop);
    }

    /**
     * Cryptographically verify Shopify HMAC signature for query parameters.
     */
    public function verifyQueryHmac(array $params): bool
    {
        $hmac = $params['hmac'] ?? null;
        if (!$hmac) {
            return false;
        }

        unset($params['hmac'], $params['signature']);
        ksort($params);

        $queryString = http_build_query($params);
        $secret = config('services.shopify.api_secret') ?: env('SHOPIFY_API_SECRET');

        if (!$secret) {
            return false;
        }

        $calculatedHmac = hash_hmac('sha256', $queryString, $secret);

        return hash_equals($calculatedHmac, $hmac);
    }

    /**
     * Build Shopify OAuth authorization redirect URL.
     */
    public function buildAuthorizationUrl(string $shop, string $state): string
    {
        $apiKey = config('services.shopify.api_key') ?: env('SHOPIFY_API_KEY');
        $scopes = config('services.shopify.scopes') ?: env('SHOPIFY_SCOPES', 'write_products,read_products,write_orders,read_orders,write_customers,read_customers');
        $redirectUri = urlencode(url('/auth/callback'));

        return "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUri}&state={$state}";
    }

    /**
     * Exchange temporary authorization code for permanent access token.
     *
     * @return array{access_token: string, scope: string}
     */
    public function exchangeCodeForAccessToken(string $shop, string $code): array
    {
        $apiKey = config('services.shopify.api_key') ?: env('SHOPIFY_API_KEY');
        $apiSecret = config('services.shopify.api_secret') ?: env('SHOPIFY_API_SECRET');

        $response = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => $apiKey,
            'client_secret' => $apiSecret,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Shopify OAuth token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Verify incoming webhook HMAC signature (base64-encoded SHA-256).
     */
    public function verifyWebhookHmac(string $payload, ?string $hmacHeader): bool
    {
        if (empty($hmacHeader)) {
            return false;
        }

        $secret = config('services.shopify.api_secret') ?: env('SHOPIFY_API_SECRET');
        if (!$secret) {
            return false;
        }

        $calculatedHmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($calculatedHmac, $hmacHeader);
    }

    /**
     * Decode and cryptographically verify a Shopify App Bridge Session Token JWT.
     *
     * @param string $token
     * @return array<string, mixed>|null Verified payload or null if invalid
     */
    public function verifySessionToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;

        $secret = config('services.shopify.api_secret') ?: env('SHOPIFY_API_SECRET');
        if (!$secret) {
            return null;
        }

        $expectedSig = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $secret, true);
        $receivedSig = $this->base64UrlDecode($sigB64);

        if (!$receivedSig || !hash_equals($expectedSig, $receivedSig)) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($payloadB64);
        if (!$payloadJson) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return null;
        }

        $now = time();

        // Validate expiration (exp) with 5-second clock skew tolerance
        if (isset($payload['exp']) && ($now - 5) >= $payload['exp']) {
            return null;
        }

        // Validate not-before (nbf)
        if (isset($payload['nbf']) && ($now + 5) < $payload['nbf']) {
            return null;
        }

        // Validate audience matches Shopify client_id / api_key if configured
        $expectedAudience = config('services.shopify.api_key') ?: env('SHOPIFY_API_KEY');
        if ($expectedAudience && isset($payload['aud']) && $payload['aud'] !== $expectedAudience) {
            return null;
        }

        // Extract and attach normalized shop domain
        $dest = $payload['dest'] ?? null;
        if ($dest) {
            $parsedHost = parse_url($dest, PHP_URL_HOST);
            if ($parsedHost) {
                $payload['shop_domain'] = $parsedHost;
            }
        }

        if (empty($payload['shop_domain']) && !empty($payload['iss'])) {
            $parsedIss = parse_url($payload['iss'], PHP_URL_HOST);
            if ($parsedIss) {
                $payload['shop_domain'] = $parsedIss;
            }
        }

        return $payload;
    }

    /**
     * Helper to decode Base64URL string.
     */
    public function base64UrlDecode(string $input): string|false
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padLen = 4 - $remainder;
            $input .= str_repeat('=', $padLen);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Helper to encode Base64URL string.
     */
    public function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }
}

