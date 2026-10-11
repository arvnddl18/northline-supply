<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class DepositCalculationService
{
    /**
     * Map of supported currency codes to standard symbols.
     */
    private const CURRENCY_SYMBOLS = [
        'AUD' => '$',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
    ];

    /**
     * Calculate deposit and balance amounts in integer cents.
     *
     * @param int $totalCents Total order or item amount in integer cents.
     * @param string $depositType 'percentage' or 'fixed'.
     * @param int|float $depositValue Percentage value (e.g. 30) or fixed amount in cents (e.g. 5000).
     * @param string $currency ISO 3-letter currency code (AUD, USD, EUR, GBP).
     * @return array{
     *     total_cents: int,
     *     deposit_cents: int,
     *     balance_cents: int,
     *     deposit_type: string,
     *     deposit_value: int|float,
     *     currency: string,
     *     formatted_total: string,
     *     formatted_deposit: string,
     *     formatted_balance: string
     * }
     */
    public function calculate(
        int $totalCents,
        string $depositType,
        int|float $depositValue,
        string $currency = 'AUD'
    ): array {
        if ($totalCents <= 0) {
            throw new InvalidArgumentException('Total amount must be greater than zero cents.');
        }

        $currency = strtoupper(trim($currency));
        $this->validateCurrency($currency);

        $normalizedType = strtolower(trim($depositType));

        return match ($normalizedType) {
            'percentage' => $this->computePercentage($totalCents, $depositValue, $currency),
            'fixed' => $this->computeFixed($totalCents, (int) $depositValue, $currency),
            default => throw new InvalidArgumentException(
                "Unsupported deposit type: {$depositType}. Expected \"percentage\" or \"fixed\"."
            ),
        };
    }

    /**
     * Convenience method to calculate percentage deposit.
     */
    public function calculatePercentage(int $totalCents, int|float $percentage, string $currency = 'AUD'): array
    {
        return $this->calculate($totalCents, 'percentage', $percentage, $currency);
    }

    /**
     * Convenience method to calculate fixed deposit.
     */
    public function calculateFixed(int $totalCents, int $depositCents, string $currency = 'AUD'): array
    {
        return $this->calculate($totalCents, 'fixed', $depositCents, $currency);
    }

    /**
     * Format an integer cents amount with currency symbol and code.
     *
     * @param int $cents Amount in cents.
     * @param string $currency Currency code (AUD, USD, EUR, GBP).
     * @return string e.g. "$200.00 AUD" or "€200.00 EUR"
     */
    public function format(int $cents, string $currency): string
    {
        $currency = strtoupper(trim($currency));
        $this->validateCurrency($currency);

        $symbol = self::CURRENCY_SYMBOLS[$currency];
        $formattedAmount = number_format($cents / 100, 2, '.', ',');

        return "{$symbol}{$formattedAmount} {$currency}";
    }

    /**
     * Compute percentage deposit with zero rounding leakage.
     */
    private function computePercentage(int $totalCents, int|float $percentage, string $currency): array
    {
        if ($percentage <= 0 || $percentage >= 100) {
            throw new InvalidArgumentException('Deposit percentage must be strictly between 0 and 100.');
        }

        $depositCents = (int) round(($totalCents * $percentage) / 100);
        $balanceCents = $totalCents - $depositCents;

        return $this->buildSummary(
            totalCents: $totalCents,
            depositCents: $depositCents,
            balanceCents: $balanceCents,
            depositType: 'percentage',
            depositValue: $percentage,
            currency: $currency
        );
    }

    /**
     * Compute fixed deposit.
     */
    private function computeFixed(int $totalCents, int $depositCents, string $currency): array
    {
        if ($depositCents <= 0) {
            throw new InvalidArgumentException('Fixed deposit amount must be greater than zero cents.');
        }

        if ($depositCents >= $totalCents) {
            throw new InvalidArgumentException('Fixed deposit must be strictly less than total amount.');
        }

        $balanceCents = $totalCents - $depositCents;

        return $this->buildSummary(
            totalCents: $totalCents,
            depositCents: $depositCents,
            balanceCents: $balanceCents,
            depositType: 'fixed',
            depositValue: $depositCents,
            currency: $currency
        );
    }

    /**
     * Build standard summary array.
     */
    private function buildSummary(
        int $totalCents,
        int $depositCents,
        int $balanceCents,
        string $depositType,
        int|float $depositValue,
        string $currency
    ): array {
        return [
            'total_cents' => $totalCents,
            'deposit_cents' => $depositCents,
            'balance_cents' => $balanceCents,
            'deposit_type' => $depositType,
            'deposit_value' => $depositValue,
            'currency' => $currency,
            'formatted_total' => $this->format($totalCents, $currency),
            'formatted_deposit' => $this->format($depositCents, $currency),
            'formatted_balance' => $this->format($balanceCents, $currency),
        ];
    }

    /**
     * Validate supported currency.
     */
    private function validateCurrency(string $currency): void
    {
        if (!array_key_exists($currency, self::CURRENCY_SYMBOLS)) {
            $supported = implode(', ', array_keys(self::CURRENCY_SYMBOLS));
            throw new InvalidArgumentException("Unsupported currency: {$currency}. Supported currencies: {$supported}.");
        }
    }
}
