<?php

namespace Tests\Feature;

use App\Jobs\ProcessStripeWebhookJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Shop;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BalancePaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_access_without_signature_or_expired_shows_clear_message(): void
    {
        $shop = Shop::create([
            'shopify_domain' => 'flow-test.myshopify.com',
            'access_token' => 'shpat_test123',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'client@example.com',
            'stripe_customer_id' => 'cus_1',
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $schedule = PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(30),
            'amount' => 14000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        // Unsigned request
        $response = $this->get("/pay/balance/{$schedule->id}");
        $response->assertStatus(200);
        $response->assertSee('This balance payment link has expired or is invalid');

        // Expired signature
        $expiredUrl = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->subMinutes(1));
        $resExpired = $this->get($expiredUrl);
        $resExpired->assertStatus(200);
        $resExpired->assertSee('This balance payment link has expired or is invalid');
    }

    public function test_valid_signed_url_renders_balance_details(): void
    {
        $shop = Shop::create([
            'shopify_domain' => 'flow-test2.myshopify.com',
            'access_token' => 'shpat_test123',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'client2@example.com',
            'stripe_customer_id' => 'cus_2',
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
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

        $validUrl = URL::signedRoute('pay.balance', ['schedule' => $schedule->id], now()->addDays(7));
        $response = $this->get($validUrl);

        $response->assertStatus(200);
        $response->assertSee('Handmade Pre-order Settlement');
        $response->assertSee('AUD 140.00');
        $response->assertSee('Pay Balance Now');
    }

    public function test_checkout_post_redirects_to_stripe_session(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_balance_session_99',
                'url' => 'https://checkout.stripe.com/pay/cs_balance_session_99',
            ], 200),
        ]);

        $shop = Shop::create([
            'shopify_domain' => 'flow-test3.myshopify.com',
            'access_token' => 'shpat_test123',
            'stripe_secret_key' => 'sk_test_mock_secret_key_12345',
            'stripe_publishable_key' => 'pk_test_mock_pub_key_12345',
            'stripe_account_id' => 'acct_test123',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'client3@example.com',
            'stripe_customer_id' => 'cus_3',
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 30000,
            'paid_amount' => 10000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $schedule = PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(25),
            'amount' => 20000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        $checkoutPostUrl = URL::signedRoute('pay.balance.checkout', ['schedule' => $schedule->id], now()->addHour());

        $response = $this->post($checkoutPostUrl);
        $response->assertRedirect('https://checkout.stripe.com/pay/cs_balance_session_99');

        Http::assertSent(function ($request) use ($schedule) {
            return str_contains($request->url(), 'checkout/sessions') &&
                $request['metadata']['type'] === 'balance_settlement' &&
                (int) $request['metadata']['schedule_id'] === $schedule->id;
        });
    }

    public function test_balance_settlement_webhook_marks_order_paid_and_syncs_shopify(): void
    {
        Http::fake([
            'https://flow-test4.myshopify.com/admin/api/2024-01/orders/*.json' => Http::response(['order' => ['id' => 112233]], 200),
        ]);

        $shop = Shop::create([
            'shopify_domain' => 'flow-test4.myshopify.com',
            'access_token' => 'shpat_test123',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'client4@example.com',
            'stripe_customer_id' => 'cus_4',
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
            'shopify_order_id' => 'gid://shopify/Order/112233',
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $schedule = PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(15),
            'amount' => 14000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        $event = WebhookEvent::create([
            'source' => 'stripe',
            'external_id' => 'evt_balance_settle_test',
            'topic' => 'checkout.session.completed',
            'payload' => [
                'type' => 'checkout.session.completed',
                'data' => [
                    'object' => [
                        'id' => 'cs_balance_done_123',
                        'payment_intent' => 'pi_balance_final_777',
                        'amount_total' => 14000,
                        'metadata' => [
                            'type' => 'balance_settlement',
                            'order_id' => $order->id,
                            'schedule_id' => $schedule->id,
                            'shop_id' => $shop->id,
                        ],
                    ],
                ],
            ],
            'status' => WebhookEvent::STATUS_PENDING,
        ]);

        $job = new ProcessStripeWebhookJob($event->id);
        $job->handle(
            app(\App\Services\Shopify\ShopifyOrderService::class),
            app(\App\Services\Email\EmailService::class)
        );

        $order->refresh();
        $schedule->refresh();

        $this->assertEquals(20000, $order->paid_amount);
        $this->assertEquals(Order::STATUS_PAID, $order->payment_status);
        $this->assertTrue($order->isSettled());
        $this->assertEquals(PaymentSchedule::STATUS_SUCCEEDED, $schedule->status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'schedule_id' => $schedule->id,
            'stripe_payment_intent_id' => 'pi_balance_final_777',
            'amount' => 14000,
            'status' => 'succeeded',
        ]);

        $this->assertDatabaseHas('email_logs', [
            'order_id' => $order->id,
            'template_key' => 'balance_paid',
        ]);
    }
}
