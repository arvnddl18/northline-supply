<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontWidgetProxyTest extends TestCase
{
    use RefreshDatabase;

    protected string $shopDomain = 'northwind-ceramics.myshopify.com';
    protected Shop $shop;
    protected string $apiSecret = 'proxy_secret_key_789';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.api_secret' => $this->apiSecret,
        ]);

        $this->shop = Shop::create([
            'shopify_domain' => $this->shopDomain,
            'access_token' => 'shpat_test_token',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);
    }

    /**
     * Compute valid Shopify App Proxy HMAC-SHA256 signature for given query parameters.
     */
    protected function signProxyParams(array $params): array
    {
        ksort($params);

        $pairs = [];
        foreach ($params as $key => $val) {
            $val = is_array($val) ? implode(',', $val) : (string) $val;
            $pairs[] = "{$key}={$val}";
        }

        $payload = implode('', $pairs);
        $signature = hash_hmac('sha256', $payload, $this->apiSecret);

        $params['signature'] = $signature;
        return $params;
    }

    public function test_proxy_requires_shop_parameter(): void
    {
        $response = $this->getJson('/proxy/plan-info');
        $response->assertStatus(400)
            ->assertJson(['error' => 'Missing shop parameter.']);
    }

    public function test_proxy_rejects_nonexistent_shop(): void
    {
        $response = $this->getJson('/proxy/plan-info?shop=unknown-store.myshopify.com');
        $response->assertStatus(404)
            ->assertJson(['error' => 'Shop not found or app uninstalled.']);
    }

    public function test_proxy_verifies_hmac_signature(): void
    {
        // 1. Tampered signature
        $params = [
            'shop' => $this->shopDomain,
            'product_id' => '1001',
            'price' => '20000',
            'signature' => 'tampered_invalid_hmac_signature',
        ];
        $this->getJson('/proxy/plan-info?' . http_build_query($params))
            ->assertStatus(401)
            ->assertJson(['error' => 'Invalid proxy HMAC signature.']);

        // 2. Valid signature
        $validParams = $this->signProxyParams([
            'shop' => $this->shopDomain,
            'product_id' => '1001',
            'price' => '20000',
        ]);

        $response = $this->getJson('/proxy/plan-info?' . http_build_query($validParams));
        $response->assertStatus(200);
    }

    public function test_proxy_resolves_product_specific_target_plan(): void
    {
        // Specific plan for product 1001 (30% deposit)
        $specificPlan = $this->shop->plans()->create([
            'name' => '30% Vase Deposit',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 30,
            'active' => true,
        ]);
        $specificPlan->targets()->create(['shopify_product_id' => 'gid://shopify/Product/1001']);

        // Fallback shop-wide plan (20% deposit)
        $this->shop->plans()->create([
            'name' => '20% Shopwide Plan',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 20,
            'active' => true,
        ]);

        $params = $this->signProxyParams([
            'shop' => $this->shopDomain,
            'product_id' => '1001',
            'price' => '20000', // $200.00 AUD
        ]);

        $response = $this->getJson('/proxy/plan-info?' . http_build_query($params));

        $response->assertStatus(200)
            ->assertJson([
                'eligible' => true,
                'plan' => [
                    'id' => $specificPlan->id,
                    'name' => '30% Vase Deposit',
                    'deposit_type' => 'percentage',
                    'deposit_value' => 30,
                ],
                'calculation' => [
                    'total_cents' => 20000,
                    'deposit_cents' => 6000,
                    'balance_cents' => 14000,
                    'formatted_deposit' => '$60.00 AUD',
                    'formatted_balance' => '$140.00 AUD',
                ],
            ]);
    }

    public function test_proxy_resolves_collection_target_plan_when_no_product_target(): void
    {
        // Collection plan (25% deposit)
        $collectionPlan = $this->shop->plans()->create([
            'name' => '25% Stoneware Collection Deposit',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 25,
            'active' => true,
        ]);
        $collectionPlan->targets()->create(['shopify_collection_id' => 'gid://shopify/Collection/5001']);

        $params = $this->signProxyParams([
            'shop' => $this->shopDomain,
            'product_id' => '9999', // No product target
            'collection_ids' => '5001, 5002',
            'price' => '10000', // $100.00
        ]);

        $response = $this->getJson('/proxy/plan-info?' . http_build_query($params));

        $response->assertStatus(200)
            ->assertJson([
                'eligible' => true,
                'plan' => [
                    'id' => $collectionPlan->id,
                    'deposit_value' => 25,
                ],
                'calculation' => [
                    'deposit_cents' => 2500,
                    'balance_cents' => 7500,
                ],
            ]);
    }

    public function test_proxy_falls_back_to_shop_wide_plan_when_no_targeted_match(): void
    {
        // Shop-wide plan (fixed $50.00 = 5000 cents)
        $shopwidePlan = $this->shop->plans()->create([
            'name' => '$50 AUD Shopwide Deposit',
            'type' => 'deposit',
            'deposit_type' => 'fixed',
            'deposit_value' => 5000,
            'active' => true,
        ]);

        $params = $this->signProxyParams([
            'shop' => $this->shopDomain,
            'product_id' => 'random_item',
            'price' => '15000', // $150.00
        ]);

        $response = $this->getJson('/proxy/plan-info?' . http_build_query($params));

        $response->assertStatus(200)
            ->assertJson([
                'eligible' => true,
                'plan' => [
                    'id' => $shopwidePlan->id,
                    'deposit_type' => 'fixed',
                    'deposit_value' => 5000,
                ],
                'calculation' => [
                    'total_cents' => 15000,
                    'deposit_cents' => 5000,
                    'balance_cents' => 10000,
                ],
            ]);
    }

    public function test_proxy_ignores_inactive_plans(): void
    {
        $this->shop->plans()->create([
            'name' => 'Inactive Plan',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 40,
            'active' => false,
        ]);

        $params = $this->signProxyParams([
            'shop' => $this->shopDomain,
            'product_id' => '1001',
            'price' => '20000',
        ]);

        $response = $this->getJson('/proxy/plan-info?' . http_build_query($params));

        $response->assertStatus(200)
            ->assertJson([
                'eligible' => false,
                'message' => 'Product is not eligible for pre-order deposits.',
            ]);
    }

    public function test_proxy_rejects_zero_or_negative_price(): void
    {
        $params = $this->signProxyParams([
            'shop' => $this->shopDomain,
            'product_id' => '1001',
            'price' => '0',
        ]);

        $response = $this->getJson('/proxy/plan-info?' . http_build_query($params));
        $response->assertStatus(400)
            ->assertJson(['error' => 'Price must be greater than zero.']);
    }
}
