# Task Completion: S1-T6 Deposit Calculation Service and Tests

- **Task ID**: `S1-T6`
- **Component**: Core Deposit Calculation Engine
- **Date Completed**: 2026-10-12
- **Related Specs**: [[PRD#FR-2]], [[CONVENTIONS#Integer Cents]], [[IMPLEMENTATION_PLAN#S1-T6]]

---

## 1. Summary of Changes
Implemented the deposit calculation service adhering to integer cents arithmetic, zero penny leakage, and strict financial rounding:
1. **Service Layer (`App\Services\Payment\DepositCalculationService`)**:
   - Supports both `percentage` and `fixed` deposit types.
   - Half-up rounding on integer cents:
     ```php
     $depositCents = (int) round(($totalCents * $percentage) / 100);
     $balanceCents = $totalCents - $depositCents;
     ```
   - Zero rounding leakage guarantee: `$depositCents + $balanceCents === $totalCents`.
   - Comprehensive validation: verifies positive totals, bounded percentages (`0 < p < 100`), and fixed deposit bounds (`0 < fixed < total`).
   - Multi-currency formatting helper `format(int $cents, string $currency)` supporting AUD, USD, EUR, and GBP.
   - Standard output DTO array providing raw cents and formatted representations for the storefront widget and checkout.
2. **Automated Unit Tests (`tests/Unit/DepositCalculationServiceTest.php`)**:
   - 18 unit tests with 81 assertions developed via TDD.
   - Tested 30% deposit on $200.00 AUD producing $60.00 deposit and $140.00 balance.
   - Tested odd cents (e.g. 30% on $199.99, 33.33% on $100.00 and $199.99).
   - Tested fixed deposits ($50.00 on $200.00).
   - Validated boundary errors and unsupported currency/deposit types.

---

## 2. Verification & Testing
- Full test suite: `54 passed (221 assertions)`.
