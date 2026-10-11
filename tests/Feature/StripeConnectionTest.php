<?php

namespace Tests\Feature;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected string $shopDomain = 'stripe-conn.myshopify.com';
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.api_key' => 'test_api_key_123',
            'services.shopify.api_secret' => 'test_api_secret_456',
            'services.stripe.key' => null,
            'services.stripe.secret' => null,
        ]);

        $this->shop = Shop::create([
            'shopify_domain' => $this->shopDomain,
            'access_token' => 'shpat_valid_test_token',
            'installed_at' => now(),
            'currency' => 'AUD',
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

    public function test_stripe_endpoints_reject_unauthorized_requests(): void
    {
        $this->getJson('/api/stripe/status')->assertStatus(401);
        $this->postJson('/api/stripe/connect', [])->assertStatus(401);
        $this->postJson('/api/stripe/disconnect', [])->assertStatus(401);
    }

    public function test_status_returns_unconnected_when_no_keys_configured(): void
    {
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/stripe/status');

        $response->assertStatus(200)
            ->assertJson([
                'connected' => false,
                'stripe_account_id' => null,
                'has_custom_keys' => false,
                'publishable_key' => null,
                'currency' => 'AUD',
            ]);
    }

    public function test_status_falls_back_to_config_when_env_keys_present(): void
    {
        config([
            'services.stripe.key' => 'pk_test_env_global_1234567890',
            'services.stripe.secret' => 'sk_test_env_global_1234567890',
        ]);
        $this->shop->update(['stripe_account_id' => 'acct_env_fallback']);

        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/stripe/status');

        $response->assertStatus(200)
            ->assertJson([
                'connected' => true,
                'stripe_account_id' => 'acct_env_fallback',
                'has_custom_keys' => false,
                'test_mode' => true,
                'currency' => 'AUD',
            ]);
    }

    public function test_connect_validates_required_keys(): void
    {
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/stripe/connect', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stripe_publishable_key', 'stripe_secret_key']);
    }

    public function test_connect_rejects_malformed_key_formats(): void
    {
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/stripe/connect', [
                'stripe_publishable_key' => 'invalid_prefix_key',
                'stripe_secret_key' => 'sk_test_123456789',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Invalid Stripe Publishable Key format. Must start with pk_test_ or pk_live_.',
            ]);
    }

    public function test_connect_rejects_invalid_credentials_when_stripe_api_fails(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response([
                'error' => [
                    'message' => 'Invalid API Key provided: sk_test_invalid',
                ],
            ], 401),
        ]);

        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/stripe/connect', [
                'stripe_publishable_key' => 'pk_test_validformat123',
                'stripe_secret_key' => 'sk_test_invalidformat456',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Invalid API Key provided: sk_test_invalid',
            ]);

        $this->shop->refresh();
        $this->assertNull($this->shop->stripe_account_id);
        $this->assertNull($this->shop->stripe_publishable_key);
        $this->assertNull($this->shop->stripe_secret_key);
    }

    public function test_connect_stores_encrypted_keys_and_account_id_on_success(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response([
                'id' => 'acct_1NorthwindTest',
                'charges_enabled' => true,
                'payouts_enabled' => true,
                'default_currency' => 'aud',
                'business_profile' => [
                    'name' => 'Northwind Ceramics Melbourne',
                ],
            ], 200),
        ]);

        $token = $this->generateSessionToken();

        $pubKey = 'pk_test_51MockedNorthwindPublicKey123';
        $secKey = 'sk_test_51MockedNorthwindSecretKey456';

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/stripe/connect', [
                'stripe_publishable_key' => $pubKey,
                'stripe_secret_key' => $secKey,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Stripe credentials successfully verified and connected.',
                'status' => [
                    'connected' => true,
                    'stripe_account_id' => 'acct_1NorthwindTest',
                    'has_custom_keys' => true,
                    'test_mode' => true,
                ],
            ]);

        $this->shop->refresh();
        $this->assertEquals('acct_1NorthwindTest', $this->shop->stripe_account_id);
        $this->assertEquals($pubKey, $this->shop->stripe_publishable_key);
        $this->assertEquals($secKey, $this->shop->stripe_secret_key);
        $this->assertTrue($this->shop->isStripeConnected());

        // Verify NFR-3: Ciphertext encryption at rest in database
        $rawRow = DB::table('shops')->where('id', $this->shop->id)->first();
        $this->assertNotEquals($secKey, $rawRow->stripe_secret_key);
        $this->assertNotEquals($pubKey, $rawRow->stripe_publishable_key);
        $this->assertStringStartsWith('eyJpdiI', $rawRow->stripe_secret_key); // Standard Laravel encrypted payload
    }

    public function test_disconnect_clears_keys_and_account_id(): void
    {
        $this->shop->update([
            'stripe_account_id' => 'acct_existing_123',
            'stripe_publishable_key' => 'pk_test_sample123',
            'stripe_secret_key' => 'sk_test_sample123',
        ]);

        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/stripe/disconnect');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Stripe account disconnected.',
                'status' => [
                    'connected' => false,
                    'stripe_account_id' => null,
                    'has_custom_keys' => false,
                ],
            ]);

        $this->shop->refresh();
        $this->assertNull($this->shop->stripe_account_id);
        $this->assertNull($this->shop->stripe_publishable_key);
        $this->assertNull($this->shop->stripe_secret_key);
        $this->assertFalse($this->shop->isStripeConnected());
    }
}
