<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopifyPrivacyWebhooksTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;
    protected string $apiSecret = 'test_shopify_secret_gdpr';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.shopify.api_secret' => $this->apiSecret]);

        $this->shop = Shop::create([
            'shopify_domain' => 'privacy-store.myshopify.com',
            'access_token' => 'shpat_privacy_secret_token',
            'currency' => 'AUD',
        ]);
    }

    protected function createSignedHeaders(string $payload): array
    {
        $hmac = base64_encode(hash_hmac('sha256', $payload, $this->apiSecret, true));

        return [
            'HTTP_X_SHOPIFY_HMAC_SHA256' => $hmac,
            'HTTP_X_SHOPIFY_SHOP_DOMAIN' => $this->shop->shopify_domain,
            'HTTP_X_SHOPIFY_WEBHOOK_ID' => 'wh_' . uniqid(),
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    public function test_customer_data_request_verifies_hmac_and_records_audit(): void
    {
        $payload = json_encode([
            'shop_id' => 123456,
            'shop_domain' => $this->shop->shopify_domain,
            'customer' => [
                'id' => 987654,
                'email' => 'data_request@example.com',
                'phone' => '+61400000000',
            ],
            'orders_requested' => [101, 102],
        ]);

        // Rejection without valid HMAC
        $unauthorized = $this->call(
            'POST',
            '/webhooks/shopify/customers/data_request',
            [],
            [],
            [],
            ['HTTP_X_SHOPIFY_HMAC_SHA256' => 'invalid_sig'],
            $payload
        );
        $unauthorized->assertStatus(401);

        // Success with valid HMAC
        $response = $this->call(
            'POST',
            '/webhooks/shopify/customers/data_request',
            [],
            [],
            [],
            $this->createSignedHeaders($payload),
            $payload
        );

        $response->assertStatus(200)
            ->assertJson(['status' => 'acknowledged', 'customer_id' => 987654]);

        $this->assertDatabaseHas('webhook_events', [
            'source' => 'shopify',
            'topic' => 'customers/data_request',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'gdpr.customer_data_request',
            'target_id' => 'customer:987654',
        ]);
    }

    public function test_customer_redact_anonymizes_pii_and_clears_payment_method(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'shopify_customer_id' => '888888',
            'email' => 'person_to_delete@example.com',
            'stripe_customer_id' => 'cus_to_delete',
            'default_payment_method' => 'pm_card_secret_123',
        ]);

        $payload = json_encode([
            'shop_id' => 123456,
            'shop_domain' => $this->shop->shopify_domain,
            'customer' => [
                'id' => 888888,
                'email' => 'person_to_delete@example.com',
            ],
            'orders_to_redact' => [501],
        ]);

        $response = $this->call(
            'POST',
            '/webhooks/shopify/customers/redact',
            [],
            [],
            [],
            $this->createSignedHeaders($payload),
            $payload
        );

        $response->assertStatus(200)
            ->assertJson(['status' => 'redacted', 'customer_id' => '888888']);

        $customer->refresh();
        $this->assertStringStartsWith('redacted_', $customer->email);
        $this->assertNull($customer->default_payment_method);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'gdpr.customer_redacted',
        ]);
    }

    public function test_shop_redact_anonymizes_token_and_marks_uninstalled(): void
    {
        $payload = json_encode([
            'shop_id' => 123456,
            'shop_domain' => $this->shop->shopify_domain,
        ]);

        $response = $this->call(
            'POST',
            '/webhooks/shopify/shop/redact',
            [],
            [],
            [],
            $this->createSignedHeaders($payload),
            $payload
        );

        $response->assertStatus(200)
            ->assertJson(['status' => 'redacted', 'shop_domain' => $this->shop->shopify_domain]);

        $this->shop->refresh();
        $this->assertStringStartsWith('redacted_', $this->shop->access_token);
        $this->assertNotNull($this->shop->uninstalled_at);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'gdpr.shop_redacted',
        ]);
    }
}
