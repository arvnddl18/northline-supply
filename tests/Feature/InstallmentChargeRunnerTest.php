<?php

namespace Tests\Feature;

use App\Jobs\ProcessStripeWebhookJob;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InstallmentChargeRunnerTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;
    protected Customer $customer;
    protected Order $order;
    protected PaymentSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Mail::fake();

        $this->shop = Shop::create([
            'shopify_domain' => 'installments.myshopify.com',
            'access_token' => 'shpat_test_token',
            'currency' => 'AUD',
            'installed_at' => now(),
        ]);
        $this->shop->setStripeCredentials('pk_test_sample', 'rk_test_sample', 'acct_test');

        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'email' => 'installment_buyer@ceramics.com',
            'stripe_customer_id' => 'cus_test_runner',
            'default_payment_method' => 'pm_card_visa_saved',
        ]);

        $this->order = Order::create([
            'shop_id' => $this->shop->id,
            'shopify_order_id' => 'gid://shopify/Order/7777',
            'customer_id' => $this->customer->id,
            'currency' => 'AUD',
            'total_amount' => 30000,
            'paid_amount' => 10000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $this->schedule = PaymentSchedule::create([
            'order_id' => $this->order->id,
            'sequence' => 2,
            'due_at' => now()->subDay(), // due in the past
            'amount' => 10000,
            'status' => PaymentSchedule::STATUS_PENDING,
            'attempt_count' => 0,
        ]);
    }

    public function test_runner_processes_due_schedule_successfully(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/payment_intents*' => Http::response([
                'id' => 'pi_scheduled_success_123',
                'status' => 'succeeded',
                'amount' => 10000,
                'currency' => 'aud',
            ], 200),
            'https://installments.myshopify.com/admin/api/*' => Http::response([
                'order' => ['id' => 7777, 'tags' => 'deposit-paid, paid'],
            ], 200),
        ]);

        $exitCode = \Illuminate\Support\Facades\Artisan::call('app:process-due-schedules');
        $this->assertEquals(0, $exitCode);

        $this->schedule->refresh();
        $this->assertEquals(PaymentSchedule::STATUS_SUCCEEDED, $this->schedule->status);
        $this->assertEquals('pi_scheduled_success_123', $this->schedule->stripe_payment_intent_id);

        $this->order->refresh();
        $this->assertEquals(20000, $this->order->paid_amount);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'schedule_id' => $this->schedule->id,
            'stripe_payment_intent_id' => 'pi_scheduled_success_123',
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'installment.charge_succeeded',
        ]);
    }

    public function test_runner_retries_on_decline_and_flags_on_third_failure(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/payment_intents*' => Http::response([
                'error' => [
                    'code' => 'card_declined',
                    'message' => 'Your card was declined.',
                ],
            ], 402),
            'https://installments.myshopify.com/admin/api/*' => Http::response([
                'order' => ['id' => 7777, 'tags' => 'installment-failed'],
            ], 200),
        ]);

        // Attempt 1: Next retry in 1 day
        $this->artisan('app:process-due-schedules');
        $this->schedule->refresh();
        $this->assertEquals(1, $this->schedule->attempt_count);
        $this->assertEquals(PaymentSchedule::STATUS_PENDING, $this->schedule->status);
        $this->assertNotNull($this->schedule->next_retry_at);

        // Advance retry timestamp and simulate Attempt 2: Next retry in 2 days
        $this->schedule->update(['next_retry_at' => now()->subMinute()]);
        $this->artisan('app:process-due-schedules');
        $this->schedule->refresh();
        $this->assertEquals(2, $this->schedule->attempt_count);
        $this->assertEquals(PaymentSchedule::STATUS_PENDING, $this->schedule->status);

        // Advance retry timestamp and simulate Attempt 3: Terminal failure
        $this->schedule->update(['next_retry_at' => now()->subMinute()]);
        $this->artisan('app:process-due-schedules');
        $this->schedule->refresh();
        $this->assertEquals(3, $this->schedule->attempt_count);
        $this->assertEquals(PaymentSchedule::STATUS_FAILED, $this->schedule->status);

        $this->order->refresh();
        $this->assertEquals(Order::STATUS_FAILED, $this->order->payment_status);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'installment.terminal_failed',
        ]);
    }

    public function test_customer_card_update_via_setup_intent(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/setup_intents' => Http::response([
                'id' => 'seti_test_123',
                'client_secret' => 'seti_test_123_secret_xyz',
            ], 200),
        ]);

        // 1. Signed GET renders view
        $signedUrl = URL::signedRoute('pay.card.update', ['schedule' => $this->schedule->id], now()->addDays(7));
        $getResponse = $this->get($signedUrl);
        $getResponse->assertStatus(200);
        $getResponse->assertSee('Update Your Payment Card');

        // 2. Signed POST saves new payment method and resets schedule
        $this->schedule->update(['status' => PaymentSchedule::STATUS_FAILED, 'attempt_count' => 3]);

        $postResponse = $this->postJson($signedUrl, [
            'payment_method_id' => 'pm_card_new_remediated',
        ]);

        $postResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->customer->refresh();
        $this->assertEquals('pm_card_new_remediated', $this->customer->default_payment_method);

        $this->schedule->refresh();
        $this->assertEquals(PaymentSchedule::STATUS_PENDING, $this->schedule->status);
        $this->assertEquals(0, $this->schedule->attempt_count);
    }

    public function test_dispute_webhook_flags_order_and_creates_audit_log(): void
    {
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'stripe_payment_intent_id' => 'pi_disputed_target_123',
            'amount' => 10000,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        Http::fake([
            'https://installments.myshopify.com/admin/api/*' => Http::response([
                'order' => ['id' => 7777, 'tags' => 'dispute-opened'],
            ], 200),
        ]);

        $event = WebhookEvent::create([
            'source' => 'stripe',
            'external_id' => 'evt_dispute_test_1',
            'topic' => 'charge.dispute.created',
            'payload' => [
                'type' => 'charge.dispute.created',
                'data' => [
                    'object' => [
                        'id' => 'dp_test_1',
                        'payment_intent' => 'pi_disputed_target_123',
                        'amount' => 10000,
                        'reason' => 'fraudulent',
                    ],
                ],
            ],
            'status' => WebhookEvent::STATUS_PENDING,
            'received_at' => now(),
        ]);

        $job = new ProcessStripeWebhookJob($event->id);
        $job->handle(app(\App\Services\Shopify\ShopifyOrderService::class), app(\App\Services\Email\EmailService::class));

        $this->order->refresh();
        $this->assertEquals('disputed', $this->order->payment_status);

        $this->assertDatabaseHas('audit_logs', [
            'shop_id' => $this->shop->id,
            'action' => 'dispute.opened',
            'target_id' => (string) $this->order->id,
        ]);
    }
}
