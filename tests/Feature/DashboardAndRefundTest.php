<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardAndRefundTest extends TestCase
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
            'shopify_domain' => 'test-ceramics.myshopify.com',
            'access_token' => 'shpat_test_token_123',
            'installed_at' => now(),
            'currency' => 'AUD',
            'stripe_account_id' => 'acct_test_123',
        ]);
        $this->shop->setStripeCredentials('pk_test_sample', 'rk_test_sample', 'acct_test_123');

        $this->user = User::create([
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

    public function test_dashboard_endpoint_returns_kpi_metrics(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'buyer@example.com',
            'stripe_customer_id' => 'cus_test_1',
        ]);

        $order = Order::create([
            'shop_id' => $this->shop->id,
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        // Succeeded payment: $60.00
        Payment::create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => 'pi_deposit_123',
            'amount' => 6000,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        // Pending balance schedule: $140.00 due in 14 days
        PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(14),
            'amount' => 14000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'currency' => 'AUD',
                'kpis' => [
                    'total_collected_cents' => 6000,
                    'pending_balance_due_cents' => 14000,
                    'upcoming_scheduled_charges' => [
                        'count' => 1,
                        'total_cents' => 14000,
                    ],
                    'failed_payments' => [
                        'count' => 0,
                        'total_cents' => 0,
                    ],
                ],
            ]);
    }

    public function test_order_filtering_by_status_and_search_query(): void
    {
        $customer1 = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'alice@ceramicfans.com',
            'stripe_customer_id' => 'cus_alice',
        ]);

        $customer2 = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'bob@earthenware.org',
            'stripe_customer_id' => 'cus_bob',
        ]);

        Order::create([
            'shop_id' => $this->shop->id,
            'shopify_order_id' => 'gid://shopify/Order/1001',
            'customer_id' => $customer1->id,
            'currency' => 'AUD',
            'total_amount' => 15000,
            'paid_amount' => 5000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        Order::create([
            'shop_id' => $this->shop->id,
            'shopify_order_id' => 'gid://shopify/Order/1002',
            'customer_id' => $customer2->id,
            'currency' => 'AUD',
            'total_amount' => 30000,
            'paid_amount' => 30000,
            'payment_status' => Order::STATUS_PAID,
        ]);

        // Search by email
        $searchResponse = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->getJson('/api/orders?query=alice');

        $searchResponse->assertStatus(200);
        $this->assertCount(1, $searchResponse->json('data'));
        $this->assertEquals('alice@ceramicfans.com', $searchResponse->json('data.0.customer_email'));

        // Filter by status
        $statusResponse = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->getJson('/api/orders?status=' . Order::STATUS_PAID);

        $statusResponse->assertStatus(200);
        $this->assertCount(1, $statusResponse->json('data'));
        $this->assertEquals('bob@earthenware.org', $statusResponse->json('data.0.customer_email'));
    }

    public function test_order_refund_success_and_audit_logging(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'refundme@example.com',
            'stripe_customer_id' => 'cus_refund_1',
        ]);

        $order = Order::create([
            'shop_id' => $this->shop->id,
            'shopify_order_id' => 'gid://shopify/Order/9999',
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => 'pi_test_refund_intent',
            'amount' => 6000,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        Http::fake([
            'https://api.stripe.com/v1/refunds' => Http::response([
                'id' => 're_test_refund_999',
                'amount' => 3000,
                'status' => 'succeeded',
            ], 200),
        ]);

        // Partial refund of $30.00 (3000 cents)
        $response = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->postJson("/api/orders/{$order->id}/refund", [
                'amount' => 3000,
                'reason' => 'Customer changed mind on glaze color',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Refund processed successfully.',
                'order' => [
                    'id' => $order->id,
                    'payment_status' => Order::STATUS_PARTIALLY_REFUNDED,
                    'total_refunded' => 3000,
                ],
            ]);

        $this->assertDatabaseHas('refunds', [
            'payment_id' => $payment->id,
            'stripe_refund_id' => 're_test_refund_999',
            'amount' => 3000,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'refund.issued',
            'target_id' => (string) $order->id,
        ]);

        // Attempt second refund exceeding remaining $30.00
        $excessResponse = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->postJson("/api/orders/{$order->id}/refund", [
                'amount' => 4000,
            ]);

        $excessResponse->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    public function test_full_refund_cancels_pending_payment_schedules(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'cancelme@example.com',
            'stripe_customer_id' => 'cus_cancel_1',
        ]);

        $order = Order::create([
            'shop_id' => $this->shop->id,
            'shopify_order_id' => 'gid://shopify/Order/8888',
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => 'pi_cancel_intent',
            'amount' => 6000,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        $schedule = PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(14),
            'amount' => 14000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        Http::fake([
            'https://api.stripe.com/v1/refunds' => Http::response([
                'id' => 're_full_refund_123',
                'amount' => 6000,
                'status' => 'succeeded',
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->sessionToken}")
            ->postJson("/api/orders/{$order->id}/refund", [
                'amount' => 6000,
                'reason' => 'Customer cancelled pre-order prior to production',
            ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(Order::STATUS_REFUNDED, $order->payment_status);

        $schedule->refresh();
        $this->assertEquals(PaymentSchedule::STATUS_CANCELLED, $schedule->status);
    }
}
