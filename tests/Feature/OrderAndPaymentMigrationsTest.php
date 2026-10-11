<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAndPaymentMigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_with_schedules_and_payments(): void
    {
        $shop = Shop::create([
            'shopify_domain' => 'ceramics-test.myshopify.com',
            'access_token' => 'shpat_test12345',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'customer@example.com',
            'stripe_customer_id' => 'cus_test123',
        ]);

        $plan = Plan::create([
            'shop_id' => $shop->id,
            'name' => '30% Deposit',
            'type' => 'deposit',
            'deposit_type' => 'percentage',
            'deposit_value' => 30,
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'currency' => 'AUD',
            'total_amount' => 20000,
            'paid_amount' => 6000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $this->assertEquals(14000, $order->remainingBalance());
        $this->assertFalse($order->isSettled());

        $schedule = PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(30),
            'amount' => 14000,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'schedule_id' => $schedule->id,
            'stripe_payment_intent_id' => 'pi_test123',
            'amount' => 6000,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);

        $this->assertCount(1, $order->paymentSchedules);
        $this->assertCount(1, $order->payments);
        $this->assertEquals($order->id, $schedule->order->id);
        $this->assertEquals($order->id, $payment->order->id);
        $this->assertEquals($schedule->id, $payment->paymentSchedule->id);
    }

    public function test_webhook_events_idempotency_storage(): void
    {
        $event = WebhookEvent::create([
            'source' => 'stripe',
            'external_id' => 'evt_test_unique_123',
            'topic' => 'checkout.session.completed',
            'payload' => ['id' => 'cs_test_123', 'status' => 'complete'],
            'status' => WebhookEvent::STATUS_PENDING,
        ]);

        $this->assertEquals('pending', $event->status);
        $this->assertNull($event->processed_at);

        $event->markProcessed();
        $this->assertEquals('processed', $event->status);
        $this->assertNotNull($event->processed_at);

        // Attempting duplicate external_id triggers unique constraint violation
        $this->expectException(\Illuminate\Database\QueryException::class);
        WebhookEvent::create([
            'source' => 'stripe',
            'external_id' => 'evt_test_unique_123',
            'topic' => 'checkout.session.completed',
            'payload' => ['duplicate' => true],
        ]);
    }

    public function test_email_template_defaults_and_logging(): void
    {
        $shop = Shop::create([
            'shopify_domain' => 'ceramics-test2.myshopify.com',
            'access_token' => 'shpat_test12345',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'jane@example.com',
            'stripe_customer_id' => 'cus_jane123',
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 15000,
            'paid_amount' => 15000,
            'payment_status' => Order::STATUS_PAID,
        ]);

        $this->assertTrue($order->isSettled());

        $template = EmailTemplate::create([
            'shop_id' => $shop->id,
            'key' => EmailTemplate::KEY_DEPOSIT_RECEIVED,
            'subject' => EmailTemplate::defaultSubject(EmailTemplate::KEY_DEPOSIT_RECEIVED),
            'body' => EmailTemplate::defaultBody(EmailTemplate::KEY_DEPOSIT_RECEIVED),
        ]);

        $this->assertStringContainsString('pre-order deposit is confirmed', $template->subject);

        $log = EmailLog::create([
            'order_id' => $order->id,
            'template_key' => EmailTemplate::KEY_DEPOSIT_RECEIVED,
            'to_email' => 'jane@example.com',
            'status' => 'sent',
        ]);

        $this->assertCount(1, $order->emailLogs);
        $this->assertEquals('jane@example.com', $log->to_email);
    }
}
