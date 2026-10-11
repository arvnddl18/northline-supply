<?php

namespace Tests\Feature;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopifyInstallFlowTest extends TestCase
{
    use RefreshDatabase;

    protected string $shopDomain = 'northwind-test.myshopify.com';
    protected string $clientSecret = 'test_client_secret_xyz';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.shopify.api_secret' => $this->clientSecret]);
    }

    public function test_oauth_install_redirects_to_shopify_authorization_url(): void
    {
        $response = $this->get("/auth/install?shop={$this->shopDomain}");

        $response->assertStatus(302);
        $redirectUrl = $response->headers->get('Location');

        $this->assertStringContainsString("https://{$this->shopDomain}/admin/oauth/authorize", $redirectUrl);
        $this->assertStringContainsString('client_id=test_client_id_123', $redirectUrl);
        $this->assertStringContainsString('state=', $redirectUrl);
    }

    public function test_oauth_install_rejects_invalid_shop_domain(): void
    {
        $response = $this->get('/auth/install?shop=invalid-domain.com');
        $response->assertStatus(400);

        $response2 = $this->get('/auth/install?shop=../../exploit.myshopify.com');
        $response2->assertStatus(400);
    }

    public function test_oauth_callback_verifies_hmac_and_stores_encrypted_access_token(): void
    {
        $state = 'test_nonce_state_12345';
        $code = 'auth_code_from_shopify';
        $plainToken = 'shpat_offline_super_secret_token_777';

        Cache::put("shopify_oauth_state_{$state}", $this->shopDomain, now()->addMinutes(10));

        Http::fake([
            "https://{$this->shopDomain}/admin/oauth/access_token" => Http::response([
                'access_token' => $plainToken,
                'scope' => 'write_products,read_orders',
            ], 200),
        ]);

        $params = [
            'code' => $code,
            'shop' => $this->shopDomain,
            'state' => $state,
            'timestamp' => '1710000000',
        ];

        ksort($params);
        $queryString = http_build_query($params);
        $hmac = hash_hmac('sha256', $queryString, $this->clientSecret);

        $params['hmac'] = $hmac;

        $response = $this->get('/auth/callback?' . http_build_query($params));

        $response->assertStatus(302);

        // Verify Shop model persisted and accessible through Eloquent
        $shop = Shop::where('shopify_domain', $this->shopDomain)->first();
        $this->assertNotNull($shop);
        $this->assertTrue($shop->isInstalled());
        $this->assertEquals($plainToken, $shop->access_token);
        $this->assertNull($shop->uninstalled_at);

        // Verify that access_token is ENCRYPTED AT REST in raw database row (NFR-3)
        $rawShop = DB::table('shops')->where('shopify_domain', $this->shopDomain)->first();
        $this->assertNotEquals($plainToken, $rawShop->access_token);
        $this->assertNotEmpty($rawShop->access_token);
    }

    public function test_oauth_callback_rejects_tampered_hmac(): void
    {
        $state = 'valid_state_nonce';
        Cache::put("shopify_oauth_state_{$state}", $this->shopDomain, now()->addMinutes(10));

        $response = $this->get("/auth/callback?shop={$this->shopDomain}&code=somecode&state={$state}&hmac=tampered_invalid_hmac");

        $response->assertStatus(401);
        $this->assertDatabaseMissing('shops', ['shopify_domain' => $this->shopDomain]);
    }

    public function test_oauth_callback_rejects_invalid_state_nonce(): void
    {
        $response = $this->get("/auth/callback?shop={$this->shopDomain}&code=somecode&state=non_existent_state&hmac=somehmac");

        $response->assertStatus(403);
    }

    public function test_webhook_uninstalled_clears_token_and_marks_uninstalled(): void
    {
        // 1. Arrange: Existing active installed shop
        $shop = Shop::create([
            'shopify_domain' => $this->shopDomain,
            'access_token' => 'shpat_active_token',
            'installed_at' => now()->subDays(5),
            'currency' => 'AUD',
        ]);

        $this->assertTrue($shop->isInstalled());

        $payload = json_encode(['id' => 123456, 'domain' => $this->shopDomain]);
        $hmac = base64_encode(hash_hmac('sha256', $payload, $this->clientSecret, true));

        // 2. Act: Send app/uninstalled webhook
        $response = $this->call(
            'POST',
            '/webhooks/app/uninstalled',
            [],
            [],
            [],
            [
                'HTTP_X_Shopify_Hmac_Sha256' => $hmac,
                'HTTP_X_Shopify_Topic' => 'app/uninstalled',
                'HTTP_X_Shopify_Shop_Domain' => $this->shopDomain,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        // 3. Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'handled',
                'topic' => 'app/uninstalled',
            ]);

        $shop->refresh();
        $this->assertFalse($shop->isInstalled());
        $this->assertNull($shop->access_token);
        $this->assertNotNull($shop->uninstalled_at);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode(['id' => 123456]);

        $response = $this->call(
            'POST',
            '/webhooks/app/uninstalled',
            [],
            [],
            [],
            [
                'HTTP_X_Shopify_Hmac_Sha256' => 'invalid_signature_string',
                'HTTP_X_Shopify_Topic' => 'app/uninstalled',
                'HTTP_X_Shopify_Shop_Domain' => $this->shopDomain,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(401);
    }

    public function test_reinstallation_after_uninstall_clears_uninstalled_at_and_stores_new_token(): void
    {
        // 1. Arrange: Previously uninstalled shop
        Shop::create([
            'shopify_domain' => $this->shopDomain,
            'access_token' => null,
            'installed_at' => now()->subMonths(1),
            'uninstalled_at' => now()->subDays(10),
            'currency' => 'AUD',
        ]);

        $state = 'reinstall_state_nonce';
        $newToken = 'shpat_new_reinstalled_token_888';
        Cache::put("shopify_oauth_state_{$state}", $this->shopDomain, now()->addMinutes(10));

        Http::fake([
            "https://{$this->shopDomain}/admin/oauth/access_token" => Http::response([
                'access_token' => $newToken,
                'scope' => 'write_products',
            ], 200),
        ]);

        $params = [
            'code' => 'new_code_123',
            'shop' => $this->shopDomain,
            'state' => $state,
        ];
        ksort($params);
        $params['hmac'] = hash_hmac('sha256', http_build_query($params), $this->clientSecret);

        // 2. Act
        $response = $this->get('/auth/callback?' . http_build_query($params));
        $response->assertStatus(302);

        // 3. Assert
        $shop = Shop::where('shopify_domain', $this->shopDomain)->first();
        $this->assertTrue($shop->isInstalled());
        $this->assertNull($shop->uninstalled_at);
        $this->assertEquals($newToken, $shop->access_token);
    }
}
