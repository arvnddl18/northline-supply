<?php

namespace Tests\Feature;

use App\Jobs\ProcessStripeWebhookJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\WebhookEvent;
use App\Services\Stripe\StripeWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StripeWebhookReceiverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_rejects_webhook_with_missing_or_invalid_signature(): void
    {
        $response = $this->postJson('/webhooks/stripe', [
            'id' => 'evt_test_1',
            'type' => 'checkout.session.completed',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid webhook signature.']);
    }

    public function test_accepts_valid_signature_and_dispatches_job(): void
    {
        Queue::fake();

        $secret = 'whsec_test_secret_123';
        config(['services.stripe.webhook_secret' => $secret]);

        $payload = [
            'id' => 'evt_test_valid_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_abc',
                    'amount_total' => 6000,
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $sigHeader = StripeWebhookService::generateSignatureHeader($rawBody, $secret);

        $response = $this->call(
            'POST',
            '/webhooks/stripe',
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $sigHeader,
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawBody
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'queued', 'id' => 'evt_test_valid_123']);

        $this->assertDatabaseHas('webhook_events', [
            'external_id' => 'evt_test_valid_123',
            'source' => 'stripe',
            'status' => 'pending',
        ]);

        Queue::assertPushed(ProcessStripeWebhookJob::class);
    }

    public function test_webhook_idempotency_ignores_duplicate_event_and_returns_200(): void
    {
        Queue::fake();

        $secret = 'whsec_test_secret_123';
        config(['services.stripe.webhook_secret' => $secret]);

        $payload = [
            'id' => 'evt_duplicate_test',
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_1']],
        ];

        $rawBody = json_encode($payload);
        $sigHeader = StripeWebhookService::generateSignatureHeader($rawBody, $secret);

        // First delivery
        $res1 = $this->call(
            'POST',
            '/webhooks/stripe',
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $sigHeader, 'CONTENT_TYPE' => 'application/json'],
            $rawBody
        );
        $res1->assertStatus(200);
        $res1->assertJson(['status' => 'queued']);

        // Second delivery (replay)
        $res2 = $this->call(
            'POST',
            '/webhooks/stripe',
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $sigHeader, 'CONTENT_TYPE' => 'application/json'],
            $rawBody
        );
        $res2->assertStatus(200);
        $res2->assertJson(['status' => 'duplicate_ignored', 'id' => 'evt_duplicate_test']);

        $this->assertDatabaseCount('webhook_events', 1);
        Queue::assertPushed(ProcessStripeWebhookJob::class, 1);
    }

    public function test_process_deposit_checkout_job_creates_order_and_schedules(): void
    {
        Http::fake([
            'https://ceramics-store.myshopify.com/admin/api/2024-01/orders.json' => Http::response([
                'order' => ['id' => 987654321],
            ], 201),
            'https://ceramics-store.myshopify.com/admin/api/2024-01/orders/*.json' => Http::response([
                'order' => ['id' => 987654321],
            ], 200),
        ]);

        $shop = Shop::create([
            'shopify_domain' => 'ceramics-store.myshopify.com',
            'access_token' => 'shpat_secret_token_123',
            'currency' => 'AUD',
        ]);

        $plan = Plan::create([
            'shop_id' => $shop->id,
            'name' => '30% Deposit',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 30,
        ]);

        $event = WebhookEvent::create([
            'source' => 'stripe',
            'external_id' => 'evt_deposit_job_test',
            'topic' => 'checkout.session.completed',
            'payload' => [
                'type' => 'checkout.session.completed',
                'data' => [
                    'object' => [
                        'id' => 'cs_deposit_test',
                        'customer' => 'cus_real_customer',
                        'customer_details' => ['email' => 'emma.collector@example.com'],
                        'payment_intent' => 'pi_deposit_12345',
                        'amount_total' => 6000,
                        'metadata' => [
                            'shop_id' => $shop->id,
                            'plan_id' => $plan->id,
                            'product_id' => 'gid://shopify/Product/100',
                            'variant_id' => 'gid://shopify/ProductVariant/200',
                            'quantity' => 1,
                            'total_cents' => 20000,
                            'deposit_cents' => 6000,
                            'balance_cents' => 14000,
                            'currency' => 'AUD',
                            'title' => 'Rustic Vase',
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

        $event->refresh();
        $this->assertEquals(WebhookEvent::STATUS_PROCESSED, $event->status);

        $order = Order::where('shop_id', $shop->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(20000, $order->total_amount);
        $this->assertEquals(6000, $order->paid_amount);
        $this->assertEquals(14000, $order->remainingBalance());
        $this->assertEquals(Order::STATUS_DEPOSIT_PAID, $order->payment_status);
        $this->assertEquals('gid://shopify/Order/987654321', $order->shopify_order_id);

        // Schedule assertions
        $this->assertCount(1, $order->paymentSchedules);
        $schedule = $order->paymentSchedules->first();
        $this->assertEquals(14000, $schedule->amount);
        $this->assertEquals('pending', $schedule->status);

        // Payment assertions
        $this->assertCount(1, $order->payments);
        $payment = $order->payments->first();
        $this->assertEquals('pi_deposit_12345', $payment->stripe_payment_intent_id);
        $this->assertEquals(6000, $payment->amount);

        // Email log assertions
        $this->assertDatabaseHas('email_logs', [
            'order_id' => $order->id,
            'template_key' => 'deposit_received',
            'to_email' => 'emma.collector@example.com',
        ]);
    }
}
