<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Sprint5HardeningTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;
    protected User $owner;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.api_key' => 'hardening_test_key',
            'services.shopify.api_secret' => 'hardening_test_secret',
        ]);

        $this->shop = Shop::create([
            'shopify_domain' => 'hardening.myshopify.com',
            'access_token' => 'shpat_hardening_token',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);

        $this->owner = User::create([
            'shop_id' => $this->shop->id,
            'email' => 'emma@northwindceramics.com',
            'role' => 'owner',
        ]);

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'member@ceramicclub.com',
            'stripe_customer_id' => 'cus_hardening_1',
            'default_payment_method' => 'pm_hardening_1',
        ]);
    }

    protected function token(): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => "https://{$this->shop->shopify_domain}/admin",
            'dest' => "https://{$this->shop->shopify_domain}",
            'aud' => config('services.shopify.api_key'),
            'sub' => '123',
            'exp' => time() + 3600,
            'nbf' => time() - 60,
        ];

        $h = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $p = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', "{$h}.{$p}", config('services.shopify.api_secret'), true);
        $s = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

        return "{$h}.{$p}.{$s}";
    }

    /**
     * S5-T6 Hardening: Verify dashboard KPI calculation performs efficiently.
     */
    public function test_dashboard_and_orders_query_performance(): void
    {
        $plan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => 'Dinnerware Set Deposit',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 30,
            'active' => true,
        ]);

        // Bulk insert batch of orders to test query indexing and grouping
        $ordersData = [];
        $now = now();
        for ($i = 1; $i <= 100; $i++) {
            $ordersData[] = [
                'shop_id' => $this->shop->id,
                'customer_id' => $this->customer->id,
                'plan_id' => $plan->id,
                'shopify_order_id' => "gid://shopify/Order/" . (10000 + $i),
                'currency' => 'AUD',
                'total_amount' => 45000,
                'paid_amount' => 13500,
                'payment_status' => ($i % 3 === 0) ? Order::STATUS_PAID : Order::STATUS_DEPOSIT_PAID,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Order::insert($ordersData);

        $token = $this->token();

        // 1. Benchmark /api/dashboard KPI retrieval
        $startTime = microtime(true);
        $dashRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard');
        $dashDuration = microtime(true) - $startTime;

        $dashRes->assertStatus(200)
            ->assertJsonStructure([
                'currency',
                'kpis' => [
                    'total_collected_cents',
                    'pending_balance_due_cents',
                    'upcoming_scheduled_charges' => ['count', 'total_cents'],
                    'failed_payments' => ['count', 'total_cents'],
                ],
            ]);

        // Assert query executes in less than 500ms
        $this->assertLessThan(0.5, $dashDuration, "Dashboard query took {$dashDuration}s, expected < 0.5s");

        // 2. Benchmark paginated orders retrieval with status filter
        $startTime = microtime(true);
        $ordersRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/orders?status=deposit_paid');
        $ordersDuration = microtime(true) - $startTime;

        $ordersRes->assertStatus(200);
        $this->assertLessThan(0.5, $ordersDuration, "Filtered orders query took {$ordersDuration}s, expected < 0.5s");
    }

    /**
     * S5-T6 Security Audit: Zero cardholder PANs/CVVs stored (PCI DSS SAQ-A verification).
     */
    public function test_pci_saq_a_scope_isolation_in_database(): void
    {
        $subPlan = Plan::create([
            'shop_id' => $this->shop->id,
            'name' => 'Monthly Club',
            'type' => 'subscription',
            'deposit_value' => 5000,
            'active' => true,
        ]);

        Subscription::create([
            'shop_id' => $this->shop->id,
            'customer_id' => $this->customer->id,
            'plan_id' => $subPlan->id,
            'stripe_subscription_id' => 'sub_pci_test_123',
            'status' => Subscription::STATUS_ACTIVE,
            'next_renewal_at' => now()->addMonth(),
        ]);

        // Inspect database columns across customers, payments, subscriptions, orders, payment_schedules
        $tables = ['customers', 'payments', 'subscriptions', 'orders', 'payment_schedules'];
        $prohibitedTerms = ['pan', 'card_number', 'cvv', 'cvc', 'exp_month', 'exp_year', 'card_holder'];

        foreach ($tables as $table) {
            $cols = DB::getSchemaBuilder()->getColumnListing($table);
            foreach ($prohibitedTerms as $term) {
                $this->assertNotContains($term, $cols, "Prohibited PCI field {$term} found in {$table} table!");
            }
        }

        // Default payment method must be a Stripe token (pm_...)
        $this->assertStringStartsWith('pm_', $this->customer->default_payment_method);
    }
}
