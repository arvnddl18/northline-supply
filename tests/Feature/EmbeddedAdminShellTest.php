<?php

namespace Tests\Feature;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbeddedAdminShellTest extends TestCase
{
    use RefreshDatabase;

    protected string $shopDomain = 'northwind-ceramics.myshopify.com';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.shopify.api_key' => 'test_api_key_123',
            'services.shopify.api_secret' => 'test_api_secret_456',
        ]);
    }

    protected function generateSessionToken(array $overrides = [], ?string $secret = null): string
    {
        $secret = $secret ?? config('services.shopify.api_secret');

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = array_merge([
            'iss' => "https://{$this->shopDomain}/admin",
            'dest' => "https://{$this->shopDomain}",
            'aud' => config('services.shopify.api_key'),
            'sub' => '12345678',
            'exp' => time() + 60,
            'nbf' => time() - 5,
            'iat' => time(),
            'jti' => 'jwt-test-uuid',
            'sid' => 'session-test-uuid',
        ], $overrides);

        $headerB64 = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadB64 = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $secret, true);
        $signatureB64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "{$headerB64}.{$payloadB64}.{$signatureB64}";
    }

    public function test_root_returns_online_json_when_no_shop_parameter_given(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'online',
                'client' => 'Northwind Ceramics',
            ]);
    }

    public function test_root_rejects_invalid_shop_domain_format(): void
    {
        $response = $this->get('/?shop=malicious-domain.com');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid shop domain parameter.',
            ]);
    }

    public function test_root_redirects_uninstalled_shop_to_oauth_install(): void
    {
        $response = $this->get("/?shop={$this->shopDomain}");

        $response->assertStatus(302);
        $this->assertStringContainsString('/auth/install', $response->headers->get('Location'));
        $this->assertStringContainsString("shop={$this->shopDomain}", $response->headers->get('Location'));
    }

    public function test_root_renders_embedded_admin_view_with_csp_headers_for_installed_shop(): void
    {
        Shop::factory()->create([
            'shopify_domain' => $this->shopDomain,
            'access_token' => 'shpat_valid_test_token',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);

        $response = $this->get("/?shop={$this->shopDomain}&host=dGVzdC5teXNob3BpZnkuY29t");

        $response->assertStatus(200);
        $response->assertViewIs('app');
        $response->assertViewHas('shop', $this->shopDomain);
        $response->assertViewHas('apiKey', 'test_api_key_123');

        // Check CSP frame-ancestors & X-Frame-Options removal
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors https://admin.shopify.com https://{$this->shopDomain};", $csp);
        $this->assertFalse($response->headers->has('X-Frame-Options'));
    }

    public function test_api_overview_rejects_request_without_session_token(): void
    {
        $response = $this->getJson('/api/overview');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Missing Shopify session token in request header.',
            ]);
    }

    public function test_api_overview_rejects_tampered_signature_token(): void
    {
        $tamperedToken = $this->generateSessionToken([], 'wrong_secret');

        $response = $this->withHeader('Authorization', "Bearer {$tamperedToken}")
            ->getJson('/api/overview');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid or expired Shopify session token.',
            ]);
    }

    public function test_api_overview_rejects_expired_session_token(): void
    {
        $expiredToken = $this->generateSessionToken([
            'exp' => time() - 30, // expired 30s ago
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$expiredToken}")
            ->getJson('/api/overview');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid or expired Shopify session token.',
            ]);
    }

    public function test_api_overview_rejects_uninstalled_shop_session(): void
    {
        // Shop does not exist in database
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/overview');

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Shop not found or application uninstalled.',
            ]);
    }

    public function test_api_overview_returns_shop_status_and_checklist_for_valid_token(): void
    {
        $shop = Shop::factory()->create([
            'shopify_domain' => $this->shopDomain,
            'access_token' => 'shpat_valid_test_token',
            'installed_at' => now(),
            'currency' => 'AUD',
            'stripe_account_id' => 'acct_test123',
        ]);

        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/overview');

        $response->assertStatus(200)
            ->assertJson([
                'shop' => [
                    'id' => $shop->id,
                    'shopify_domain' => $this->shopDomain,
                    'currency' => 'AUD',
                    'stripe_connected' => true,
                    'plans_count' => 0,
                ],
                'checklist' => [
                    'app_installed' => true,
                    'stripe_connected' => true,
                    'plans_configured' => false,
                    'ready_for_storefront' => false,
                ],
            ]);
    }
}
