<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shop;
use Database\Seeders\Sprint3DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint3DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.api_key' => 'test_key',
            'services.shopify.api_secret' => 'test_secret',
        ]);
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

    public function test_sprint3_demo_seeder_executes_and_populates_all_payment_states(): void
    {
        $this->seed(Sprint3DemoSeeder::class);

        $shop = Shop::where('shopify_domain', 'northwind-ceramics.myshopify.com')->first();
        $this->assertNotNull($shop);

        // Assert presence of all required states
        $statuses = Order::where('shop_id', $shop->id)->pluck('payment_status')->all();
        $this->assertContains(Order::STATUS_DEPOSIT_PAID, $statuses);
        $this->assertContains(Order::STATUS_PAID, $statuses);
        $this->assertContains(Order::STATUS_PARTIALLY_REFUNDED, $statuses);
        $this->assertContains(Order::STATUS_REFUNDED, $statuses);
        $this->assertContains(Order::STATUS_FAILED, $statuses);
        $this->assertContains(Order::STATUS_PENDING_DEPOSIT, $statuses);

        // Assert dashboard endpoint aggregates correctly for Emma's store
        $token = $this->generateTestSessionToken($shop->shopify_domain);
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'currency',
                'kpis' => [
                    'total_collected_cents',
                    'pending_balance_due_cents',
                    'upcoming_scheduled_charges' => ['count', 'total_cents'],
                    'failed_payments' => ['count', 'total_cents'],
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('kpis.total_collected_cents'));
        $this->assertGreaterThan(0, $response->json('kpis.pending_balance_due_cents'));
    }
}
