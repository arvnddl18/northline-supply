<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BalanceRemindersSchedulerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_send_balance_reminders_command_sends_email_and_prevents_duplicate(): void
    {
        $shop = Shop::create([
            'shopify_domain' => 'reminder-test.myshopify.com',
            'access_token' => 'shpat_reminder_123',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'artisan.buyer@example.com',
            'stripe_customer_id' => 'cus_rem_1',
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'currency' => 'AUD',
            'total_amount' => 25000,
            'paid_amount' => 7500,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        // Schedule due in 5 days (within 7-day reminder window)
        $schedule = PaymentSchedule::create([
            'order_id' => $order->id,
            'sequence' => 1,
            'due_at' => now()->addDays(5),
            'amount' => 17500,
            'status' => PaymentSchedule::STATUS_PENDING,
        ]);

        // 1. First run of reminder command
        $this->artisan('app:send-balance-reminders', ['--days' => 7])
            ->expectsOutputToContain('Completed reminder run: 1 sent, 0 skipped')
            ->assertExitCode(0);

        $this->assertDatabaseHas('email_logs', [
            'order_id' => $order->id,
            'template_key' => 'balance_due',
            'to_email' => 'artisan.buyer@example.com',
            'status' => 'sent',
        ]);

        Mail::assertSent(\App\Mail\OrderTransactionalMail::class, 1);

        // 2. Second run on same day (enforces US-06: No second email goes out for the same reminder step)
        $this->artisan('app:send-balance-reminders', ['--days' => 7])
            ->expectsOutputToContain('Completed reminder run: 0 sent, 1 skipped')
            ->assertExitCode(0);

        // Still only 1 email log and 1 sent mail
        $this->assertDatabaseCount('email_logs', 1);
        Mail::assertSent(\App\Mail\OrderTransactionalMail::class, 1);
    }
}
