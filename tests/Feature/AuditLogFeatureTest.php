<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;
    protected User $user;
    protected string $sessionToken;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.api_key' => 'test_key',
            'services.shopify.api_secret' => 'test_secret',
        ]);

        $this->shop = Shop::create([
            'shopify_domain' => 'audit-store.myshopify.com',
            'access_token' => 'shpat_audit_test_token',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);

        $this->user = User::create([
            'name' => 'Emma Carter',
            'shop_id' => $this->shop->id,
            'email' => 'emma@northwindceramics.com',
            'role' => 'owner',
        ]);

        $this->sessionToken = $this->generateTestSessionToken($this->shop->shopify_domain);
    }

    protected function generateTestSessionToken(string $shopDomain): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => "https://{$shopDomain}/admin",
            'dest' => "https://{$shopDomain}",
            'aud' => config('services.shopify.api_key'),
            'sub' => '12345678',
            'exp' => time() + 3600,
            'nbf' => time() - 60,
            'iat' => time(),
        ];
        $h = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $p = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $secret = config('services.shopify.api_secret');
        $s = rtrim(strtr(base64_encode(hash_hmac('sha256', "{$h}.{$p}", $secret, true)), '+/', '-_'), '=');

        return "{$h}.{$p}.{$s}";
    }

    public function test_audit_logs_endpoint_returns_paginated_records(): void
    {
        AuditLog::record(
            shopId: $this->shop->id,
            action: 'plan.created',
            target: 'Plan#1',
            meta: ['name' => '30% Deposit'],
            userId: $this->user->id
        );

        AuditLog::record(
            shopId: $this->shop->id,
            action: 'refund.issued',
            target: 'Order#1001',
            meta: ['amount' => 5000],
            userId: $this->user->id
        );

        $response = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->getJson('/api/audit-logs');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals('refund.issued', $response->json('data.0.action'));
    }

    public function test_audit_logs_endpoint_filters_by_action(): void
    {
        AuditLog::record(
            shopId: $this->shop->id,
            action: 'plan.created',
            target: 'Plan#1',
            meta: ['name' => '30% Deposit']
        );

        AuditLog::record(
            shopId: $this->shop->id,
            action: 'refund.issued',
            target: 'Order#1001',
            meta: ['amount' => 5000]
        );

        $response = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->getJson('/api/audit-logs?action=plan.created');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('plan.created', $response->json('data.0.action'));
    }

    public function test_audit_logs_enforces_strict_multi_tenant_isolation(): void
    {
        $otherShop = Shop::create([
            'shopify_domain' => 'other-pottery.myshopify.com',
            'access_token' => 'shpat_other_pottery',
            'currency' => 'AUD',
        ]);

        AuditLog::record(
            shopId: $otherShop->id,
            action: 'secret.other_action',
            target: 'SecretTarget',
            meta: ['sensitive' => 'data']
        );

        AuditLog::record(
            shopId: $this->shop->id,
            action: 'my.public_action',
            target: 'PublicTarget',
            meta: []
        );

        $response = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->getJson('/api/audit-logs');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('my.public_action', $response->json('data.0.action'));
    }

    public function test_plan_lifecycle_emits_audit_logs(): void
    {
        // 1. Create plan
        $createRes = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->postJson('/api/plans', [
                'name' => '25% Stoneware Deposit',
                'deposit_type' => 'percentage',
                'deposit_value' => 25,
            ]);
        $createRes->assertStatus(201);
        $planId = $createRes->json('plan.id');

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'plan.created',
            'target_id' => (string) $planId,
        ]);

        // 2. Update plan
        $updateRes = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->putJson("/api/plans/{$planId}", [
                'name' => '35% Stoneware Deposit',
                'deposit_value' => 35,
            ]);
        $updateRes->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'plan.updated',
            'target_id' => (string) $planId,
        ]);

        // 3. Delete plan
        $deleteRes = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->deleteJson("/api/plans/{$planId}");
        $deleteRes->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'plan.deleted',
            'target_id' => "plan:{$planId}",
        ]);
    }
}
