<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected string $shopDomain = 'northwind-ceramics.myshopify.com';
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.api_key' => 'test_api_key_123',
            'services.shopify.api_secret' => 'test_api_secret_456',
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

    public function test_plans_endpoints_reject_unauthorized_requests(): void
    {
        $this->getJson('/api/plans')->assertStatus(401);
        $this->postJson('/api/plans', [])->assertStatus(401);
        $this->postJson('/api/plans/preview', [])->assertStatus(401);
        $this->getJson('/api/plans/1')->assertStatus(401);
        $this->putJson('/api/plans/1', [])->assertStatus(401);
        $this->deleteJson('/api/plans/1')->assertStatus(401);
    }

    public function test_can_list_empty_plans_for_shop(): void
    {
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/plans');

        $response->assertStatus(200)
            ->assertJson([
                'plans' => [],
                'currency' => 'AUD',
            ]);
    }

    public function test_can_create_percentage_deposit_plan_with_targets(): void
    {
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/plans', [
                'name' => '30% Custom Ceramic Vase Pre-order',
                'type' => 'deposit',
                'deposit_type' => 'percentage',
                'deposit_value' => 30,
                'product_ids' => ['gid://shopify/Product/1001', 'gid://shopify/Product/1002'],
                'collection_ids' => ['gid://shopify/Collection/5001'],
                'active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Plan created successfully.',
                'plan' => [
                    'name' => '30% Custom Ceramic Vase Pre-order',
                    'type' => 'deposit',
                    'deposit_type' => 'percentage',
                    'deposit_value' => 30,
                    'active' => true,
                ],
            ]);

        $this->assertDatabaseHas('plans', [
            'shop_id' => $this->shop->id,
            'name' => '30% Custom Ceramic Vase Pre-order',
            'deposit_type' => 'percentage',
            'deposit_value' => 30,
        ]);

        $planId = $response->json('plan.id');
        $this->assertDatabaseHas('plan_targets', [
            'plan_id' => $planId,
            'shopify_product_id' => 'gid://shopify/Product/1001',
        ]);
        $this->assertDatabaseHas('plan_targets', [
            'plan_id' => $planId,
            'shopify_collection_id' => 'gid://shopify/Collection/5001',
        ]);
    }

    public function test_can_create_fixed_deposit_plan(): void
    {
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/plans', [
                'name' => '$50 AUD Fixed Deposit for Dinner Sets',
                'type' => 'deposit',
                'deposit_type' => 'fixed',
                'deposit_value' => 5000, // $50.00 in integer cents
                'active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'plan' => [
                    'deposit_type' => 'fixed',
                    'deposit_value' => 5000,
                ],
            ]);

        $this->assertDatabaseHas('plans', [
            'shop_id' => $this->shop->id,
            'deposit_type' => 'fixed',
            'deposit_value' => 5000,
        ]);
    }

    public function test_validation_rejects_invalid_deposit_values(): void
    {
        $token = $this->generateSessionToken();

        // 100% percentage is invalid (must be strictly < 100)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/plans', [
                'name' => 'Invalid Plan',
                'deposit_type' => 'percentage',
                'deposit_value' => 100,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deposit_value']);

        // 0% percentage is invalid
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/plans', [
                'name' => 'Invalid Plan',
                'deposit_type' => 'percentage',
                'deposit_value' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deposit_value']);
    }

    public function test_can_show_and_update_plan_and_sync_targets(): void
    {
        $plan = $this->shop->plans()->create([
            'name' => 'Original Plan',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 25,
            'active' => true,
        ]);
        $plan->targets()->create(['shopify_product_id' => 'gid://shopify/Product/999']);

        $token = $this->generateSessionToken();

        // Show
        $showRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/plans/{$plan->id}");
        $showRes->assertStatus(200)
            ->assertJson(['plan' => ['name' => 'Original Plan']]);

        // Update
        $updateRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/plans/{$plan->id}", [
                'name' => 'Updated Plan Name',
                'deposit_value' => 35,
                'active' => false,
                'product_ids' => ['gid://shopify/Product/888'],
            ]);

        $updateRes->assertStatus(200)
            ->assertJson([
                'plan' => [
                    'name' => 'Updated Plan Name',
                    'deposit_value' => 35,
                    'active' => false,
                ],
            ]);

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Updated Plan Name',
            'deposit_value' => 35,
            'active' => false,
        ]);

        // Targets synced
        $this->assertDatabaseMissing('plan_targets', ['shopify_product_id' => 'gid://shopify/Product/999']);
        $this->assertDatabaseHas('plan_targets', ['shopify_product_id' => 'gid://shopify/Product/888']);
    }

    public function test_can_delete_plan_cascades_targets(): void
    {
        $plan = $this->shop->plans()->create([
            'name' => 'Plan to Delete',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 20,
            'active' => true,
        ]);
        $target = $plan->targets()->create(['shopify_product_id' => 'gid://shopify/Product/777']);

        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Plan deleted successfully.']);

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
        $this->assertDatabaseMissing('plan_targets', ['id' => $target->id]);
    }

    public function test_multi_tenant_isolation_cannot_access_or_modify_other_shop_plan(): void
    {
        $otherShop = Shop::create([
            'shopify_domain' => 'other-ceramic-store.myshopify.com',
            'access_token' => 'shpat_other_store_token',
            'installed_at' => now(),
        ]);
        $otherPlan = $otherShop->plans()->create([
            'name' => 'Other Shop Confidential Plan',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 40,
            'active' => true,
        ]);

        $token = $this->generateSessionToken();

        // Attempt show
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/plans/{$otherPlan->id}")
            ->assertStatus(404);

        // Attempt update
        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/plans/{$otherPlan->id}", ['name' => 'Hacked Name'])
            ->assertStatus(404);

        // Attempt delete
        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/plans/{$otherPlan->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('plans', [
            'id' => $otherPlan->id,
            'name' => 'Other Shop Confidential Plan',
        ]);
    }

    public function test_preview_endpoint_computes_breakdown(): void
    {
        $token = $this->generateSessionToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/plans/preview', [
                'deposit_type' => 'percentage',
                'deposit_value' => 30,
                'sample_total_cents' => 20000, // $200.00 AUD
                'currency' => 'AUD',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'total_cents' => 20000,
                'deposit_cents' => 6000,
                'balance_cents' => 14000,
                'formatted_total' => '$200.00 AUD',
                'formatted_deposit' => '$60.00 AUD',
                'formatted_balance' => '$140.00 AUD',
            ]);
    }
}
