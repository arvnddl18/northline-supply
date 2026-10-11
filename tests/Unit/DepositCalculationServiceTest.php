<?php

namespace Tests\Unit;

use App\Services\Payment\DepositCalculationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DepositCalculationServiceTest extends TestCase
{
    private DepositCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DepositCalculationService();
    }

    /**
     * Standard 30% deposit on $200.00 AUD ($60.00 deposit, $140.00 balance).
     */
    public function test_standard_30_percent_deposit_on_200_aud(): void
    {
        $result = $this->service->calculate(
            totalCents: 20000,
            depositType: 'percentage',
            depositValue: 30,
            currency: 'AUD'
        );

        $this->assertSame(20000, $result['total_cents']);
        $this->assertSame(6000, $result['deposit_cents']);
        $this->assertSame(14000, $result['balance_cents']);
        $this->assertSame('percentage', $result['deposit_type']);
        $this->assertSame(30, $result['deposit_value']);
        $this->assertSame('AUD', $result['currency']);
        $this->assertSame('$200.00 AUD', $result['formatted_total']);
        $this->assertSame('$60.00 AUD', $result['formatted_deposit']);
        $this->assertSame('$140.00 AUD', $result['formatted_balance']);
        $this->assertSame($result['total_cents'], $result['deposit_cents'] + $result['balance_cents']);
    }

    /**
     * Odd cents rounding: guarantee deposit_cents + balance_cents === total_cents with zero leakage.
     */
    public function test_odd_cents_rounding_guarantees_zero_leakage(): void
    {
        // Case 1: 30% on $199.99 (19999 cents)
        // 19999 * 0.30 = 5999.7 -> round to 6000 cents
        // balance = 19999 - 6000 = 13999 cents
        $res1 = $this->service->calculate(19999, 'percentage', 30, 'AUD');
        $this->assertSame(6000, $res1['deposit_cents']);
        $this->assertSame(13999, $res1['balance_cents']);
        $this->assertSame(19999, $res1['deposit_cents'] + $res1['balance_cents']);
        $this->assertSame('$199.99 AUD', $res1['formatted_total']);
        $this->assertSame('$60.00 AUD', $res1['formatted_deposit']);
        $this->assertSame('$139.99 AUD', $res1['formatted_balance']);

        // Case 2: 33.33% on $100.00 (10000 cents)
        // 10000 * 0.3333 = 3333.0 -> 3333 cents
        // balance = 10000 - 3333 = 6667 cents
        $res2 = $this->service->calculate(10000, 'percentage', 33.33, 'AUD');
        $this->assertSame(3333, $res2['deposit_cents']);
        $this->assertSame(6667, $res2['balance_cents']);
        $this->assertSame(10000, $res2['deposit_cents'] + $res2['balance_cents']);

        // Case 3: 33.33% on $199.99 (19999 cents)
        // 19999 * 0.3333 = 6665.6667 -> round to 6666 cents
        // balance = 19999 - 6666 = 13333 cents
        $res3 = $this->service->calculate(19999, 'percentage', 33.33, 'AUD');
        $this->assertSame(6666, $res3['deposit_cents']);
        $this->assertSame(13333, $res3['balance_cents']);
        $this->assertSame(19999, $res3['deposit_cents'] + $res3['balance_cents']);

        // Case 4: 1 cent total
        // 30% of 1 cent = 0.3 -> 0 cents? If 0 cents, deposit must be strictly > 0, so test boundary
    }

    /**
     * Standard percentage tiers: 10%, 25%, 50%, 75%.
     */
    public function test_standard_percentage_tiers(): void
    {
        $tiers = [
            10 => [1000, 9000],
            25 => [2500, 7500],
            50 => [5000, 5000],
            75 => [7500, 2500],
        ];

        foreach ($tiers as $pct => [$expectedDeposit, $expectedBalance]) {
            $result = $this->service->calculate(10000, 'percentage', $pct, 'USD');
            $this->assertSame($expectedDeposit, $result['deposit_cents'], "Failed on {$pct}%");
            $this->assertSame($expectedBalance, $result['balance_cents'], "Failed on {$pct}%");
            $this->assertSame(10000, $result['deposit_cents'] + $result['balance_cents']);
            $this->assertSame('USD', $result['currency']);
        }
    }

    /**
     * Fixed deposit amounts: e.g. $50.00 on $200.00 -> $50.00 deposit, $150.00 balance.
     */
    public function test_fixed_deposit_calculation(): void
    {
        $result = $this->service->calculate(
            totalCents: 20000,
            depositType: 'fixed',
            depositValue: 5000,
            currency: 'AUD'
        );

        $this->assertSame(20000, $result['total_cents']);
        $this->assertSame(5000, $result['deposit_cents']);
        $this->assertSame(15000, $result['balance_cents']);
        $this->assertSame('fixed', $result['deposit_type']);
        $this->assertSame(5000, $result['deposit_value']);
        $this->assertSame('$200.00 AUD', $result['formatted_total']);
        $this->assertSame('$50.00 AUD', $result['formatted_deposit']);
        $this->assertSame('$150.00 AUD', $result['formatted_balance']);
        $this->assertSame(20000, $result['deposit_cents'] + $result['balance_cents']);
    }

    /**
     * Helper methods: calculatePercentage and calculateFixed.
     */
    public function test_convenience_methods(): void
    {
        $pctResult = $this->service->calculatePercentage(20000, 30, 'AUD');
        $this->assertSame(6000, $pctResult['deposit_cents']);
        $this->assertSame(14000, $pctResult['balance_cents']);

        $fixedResult = $this->service->calculateFixed(20000, 5000, 'AUD');
        $this->assertSame(5000, $fixedResult['deposit_cents']);
        $this->assertSame(15000, $fixedResult['balance_cents']);
    }

    /**
     * Edge case: Zero or negative total cents throws InvalidArgumentException.
     */
    public function test_throws_exception_on_zero_or_negative_total(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total amount must be greater than zero cents.');
        $this->service->calculate(0, 'percentage', 30);
    }

    public function test_throws_exception_on_negative_total(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total amount must be greater than zero cents.');
        $this->service->calculate(-500, 'percentage', 30);
    }

    /**
     * Edge cases: percentage <= 0 or >= 100.
     */
    public function test_throws_exception_on_zero_percentage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Deposit percentage must be strictly between 0 and 100.');
        $this->service->calculate(20000, 'percentage', 0);
    }

    public function test_throws_exception_on_100_percentage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Deposit percentage must be strictly between 0 and 100.');
        $this->service->calculate(20000, 'percentage', 100);
    }

    public function test_throws_exception_on_negative_percentage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Deposit percentage must be strictly between 0 and 100.');
        $this->service->calculate(20000, 'percentage', -10);
    }

    public function test_throws_exception_on_greater_than_100_percentage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Deposit percentage must be strictly between 0 and 100.');
        $this->service->calculate(20000, 'percentage', 105);
    }

    /**
     * Edge cases: fixed deposit <= 0, equals total, or exceeds total.
     */
    public function test_throws_exception_on_zero_fixed_deposit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fixed deposit amount must be greater than zero cents.');
        $this->service->calculate(20000, 'fixed', 0);
    }

    public function test_throws_exception_on_negative_fixed_deposit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fixed deposit amount must be greater than zero cents.');
        $this->service->calculate(20000, 'fixed', -100);
    }

    public function test_throws_exception_when_fixed_deposit_equals_total(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fixed deposit must be strictly less than total amount.');
        $this->service->calculate(20000, 'fixed', 20000);
    }

    public function test_throws_exception_when_fixed_deposit_exceeds_total(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fixed deposit must be strictly less than total amount.');
        $this->service->calculate(20000, 'fixed', 25000);
    }

    /**
     * Edge case: Unsupported deposit type.
     */
    public function test_throws_exception_on_unsupported_deposit_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported deposit type: recurring. Expected "percentage" or "fixed".');
        $this->service->calculate(20000, 'recurring', 30);
    }

    /**
     * Multi-currency formatting across AUD, USD, EUR, GBP.
     */
    public function test_multi_currency_formatting(): void
    {
        $this->assertSame('$200.00 AUD', $this->service->format(20000, 'AUD'));
        $this->assertSame('$200.00 USD', $this->service->format(20000, 'USD'));
        $this->assertSame('€200.00 EUR', $this->service->format(20000, 'EUR'));
        $this->assertSame('£200.00 GBP', $this->service->format(20000, 'GBP'));

        // Case insensitivity
        $this->assertSame('$200.00 AUD', $this->service->format(20000, 'aud'));
        $this->assertSame('£140.50 GBP', $this->service->format(14050, 'gbp'));
    }

    /**
     * Edge case: Unsupported currency code.
     */
    public function test_throws_exception_on_unsupported_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency: CAD. Supported currencies: AUD, USD, EUR, GBP.');
        $this->service->format(20000, 'CAD');
    }
}
