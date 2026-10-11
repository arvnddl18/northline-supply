<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Shop;
use App\Services\Payments\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class InstallmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InstallmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InstallmentService();
    }

    public function test_equal_division_without_remainder(): void
    {
        // $300.00 divided into 3 payments = 10000, 10000, 10000
        $result = $this->service->calculateSchedule(30000, 3);
        $this->assertEquals([10000, 10000, 10000], $result);
        $this->assertEquals(30000, array_sum($result));
    }

    public function test_odd_cents_distribution_guarantees_zero_penny_leakage(): void
    {
        // $100.00 (10000 cents) divided into 3 payments:
        // 10000 / 3 = 3333 with remainder 1 -> [3334, 3333, 3333]
        $result3 = $this->service->calculateSchedule(10000, 3);
        $this->assertEquals([3334, 3333, 3333], $result3);
        $this->assertEquals(10000, array_sum($result3));

        // $100.02 (10002 cents) divided into 4 payments:
        // 10002 / 4 = 2500 with remainder 2 -> [2501, 2501, 2500, 2500]
        $result4 = $this->service->calculateSchedule(10002, 4);
        $this->assertEquals([2501, 2501, 2500, 2500], $result4);
        $this->assertEquals(10002, array_sum($result4));
    }

    public function test_throws_exception_on_invalid_installment_counts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->calculateSchedule(10000, 1);
    }

    public function test_throws_exception_on_excessive_installment_counts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->calculateSchedule(10000, 5);
    }

    public function test_throws_exception_on_zero_total(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->calculateSchedule(0, 3);
    }

    public function test_generates_schedules_with_correct_intervals(): void
    {
        $shop = Shop::create([
            'shopify_domain' => 'installments.myshopify.com',
            'access_token' => 'shpat_test',
            'currency' => 'AUD',
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'email' => 'client@ceramics.com',
            'stripe_customer_id' => 'cus_1',
        ]);

        $plan = Plan::create([
            'shop_id' => $shop->id,
            'name' => '3 Payments Over 6 Weeks',
            'type' => 'installment',
            'installments_count' => 3,
            'interval' => 'week',
            'interval_count' => 2,
            'active' => true,
        ]);

        $order = Order::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'currency' => 'AUD',
            'total_amount' => 30000,
            'paid_amount' => 10000,
            'payment_status' => Order::STATUS_DEPOSIT_PAID,
        ]);

        $start = now();
        $schedules = $this->service->generateSchedules($order, $plan, $start);

        $this->assertCount(3, $schedules);
        
        // Sequence 1: Paid at checkout
        $this->assertEquals(1, $schedules[0]->sequence);
        $this->assertEquals(10000, $schedules[0]->amount);
        $this->assertEquals('succeeded', $schedules[0]->status);
        $this->assertEquals($start->toDateTimeString(), $schedules[0]->due_at->toDateTimeString());

        // Sequence 2: 2 weeks later
        $this->assertEquals(2, $schedules[1]->sequence);
        $this->assertEquals(10000, $schedules[1]->amount);
        $this->assertEquals('pending', $schedules[1]->status);
        $this->assertEquals($start->copy()->addWeeks(2)->toDateTimeString(), $schedules[1]->due_at->toDateTimeString());

        // Sequence 3: 4 weeks later
        $this->assertEquals(3, $schedules[2]->sequence);
        $this->assertEquals(10000, $schedules[2]->amount);
        $this->assertEquals('pending', $schedules[2]->status);
        $this->assertEquals($start->copy()->addWeeks(4)->toDateTimeString(), $schedules[2]->due_at->toDateTimeString());
    }
}
