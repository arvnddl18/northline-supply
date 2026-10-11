<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplatesAndOrdersApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $shopDomain = 'api-test.myshopify.com';
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        config([
            'services.shopify.api_key' => 'test_key',
            'services.shopify.api_secret' => 'test_secret',
        ]);

        $this->shop = Shop::create([
            'shopify_domain' => $this->shopDomain,
            'access_token' => 'shpat_test_token',
            'installed_at' => now(),
            'currency' => 'AUD',
        ]);
    }

    protected function token(): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => "https://{$this->shopDomain}/admin",
            'dest' => "https://{$this->shopDomain}",
            'aud' => config('services.shopify.api_key'),
            'sub' => '123',
            'exp' => time() + 60,
            'nbf' => time() - 5,
        ];

        $h = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $p = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $s = rtrim(strtr(base64_encode(hash_hmac('sha256', "{$h}.{$p}", config('services.shopify.api_secret'), true)), '+/', '-_'), '=');

        return "{$h}.{$p}.{$s}";
    }

    public function test_can_list_orders_with_payment_status(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'buyer@example.com',
            'stripe_customer_id' => 'cus_test',
        ]);

        $order = Order::create([
            'shop_id' => $this->shop->id,
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(20),
            'amount' => 14000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token())
            ->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'remaining_balance' => 14000,
            'payment_status' => 'deposit_paid',
            'status_label' => 'Deposit Paid — Balance Due',
        ]);
    }

    public function test_can_generate_manual_payment_link(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'buyer@example.com',
            'stripe_customer_id' => 'cus_test',
        ]);

        $order = Order::create([
            'shop_id' => $this->shop->id,
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $schedule = PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(20),
            'amount' => 14000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token())
            ->postJson("/api/orders/{$order->id}/payment-link");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'order_id',
            'schedule_id',
            'amount',
            'payment_link',
            'expires_at',
        ]);

        $link = $response->json('payment_link');
        $this->assertStringContainsString("/pay/balance/{$schedule->id}", $link);
        $this->assertStringContainsString('signature=', $link);
    }

    public function test_email_templates_crud_preview_and_test_send(): void
    {
        // 1. List defaults
        $resList = $this->withHeader('Authorization', 'Bearer ' . $this->token())
            ->getJson('/api/email-templates');

        $resList->assertStatus(200);
        $resList->assertJsonCount(3, 'templates');

        // 2. Update template
        $resUpdate = $this->withHeader('Authorization', 'Bearer ' . $this->token())
            ->putJson('/api/email-templates/deposit_received', [
                'subject' => 'Custom Subject for #{order_number}',
                'body' => 'Custom body with balance: {balance_amount}',
            ]);

        $resUpdate->assertStatus(200);
        $resUpdate->assertJson([
            'status' => 'saved',
            'template' => [
                'key' => 'deposit_received',
                'subject' => 'Custom Subject for #{order_number}',
            ],
        ]);

        // 3. Preview template
        $resPreview = $this->withHeader('Authorization', 'Bearer ' . $this->token())
            ->postJson('/api/email-templates/deposit_received/preview');

        $resPreview->assertStatus(200);
        $resPreview->assertJsonFragment([
            'subject' => 'Custom Subject for #1042',
            'body' => 'Custom body with balance: AUD 140.00',
        ]);

        // 4. Test Send
        $resSend = $this->withHeader('Authorization', 'Bearer ' . $this->token())
            ->postJson('/api/email-templates/deposit_received/test-send', [
                'email' => 'emma@northwindceramics.com',
            ]);

        $resSend->assertStatus(200);
        $resSend->assertJson(['status' => 'sent', 'recipient' => 'emma@northwindceramics.com']);

        Mail::assertSent(\App\Mail\OrderTransactionalMail::class);
    }
}
