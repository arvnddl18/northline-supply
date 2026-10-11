# ADR 006: Sprint 3 (Phase 3 MVP) Architecture & Financial Governance

## Status
Accepted

## Date
2026-10-12

## Context
Following the successful completion of Sprint 0 (Infrastructure), Sprint 1 (Onboarding, Plans & Storefront Widget), and Sprint 2 (Webhooks, Orders, Balance Link & Emails), the project enters Sprint 3 (Phase 3 MVP milestone). This sprint encompasses:
1. **S3-T1**: Merchant dashboard & financial KPIs (US-07).
2. **S3-T2**: Order list filtering, searching, and status views (US-07).
3. **S3-T3**: Refund processing across Stripe and Shopify (US-08).
4. **S3-T4**: Mandatory Shopify GDPR / Privacy webhooks (US-23).
5. **S3-T5**: Multi-tenant administrative audit log (US-24).
6. **S3-T6**: Client demonstration dataset & seeder (Emma Carter check-in).

A formal design grill-down was conducted to resolve dependencies and establish the financial and security boundaries.

## Decisions

### 1. Granular Payment Refund Architecture (US-08 / S3-T3)
- Refunds are issued at the individual payment transaction level (`payments` record) rather than blindly aggregate across the order.
- Supports both full payment refunds and partial refunds (integer cents validation, bounded by `$payment->amount - $payment->refunded_amount`).
- **Schedule Cancellation Rule**: If a deposit payment is refunded while the corresponding order's balance schedule is still pending or due, the payment schedule is automatically cancelled (`cancelled` status, `cancelled_at` timestamp) to prevent unintended automatic charging or reminder emails.
- **Dual Gateway Synchronization**:
  1. Issues refund via Stripe API (`POST /v1/refunds`) using the payment's `stripe_charge_id` or `stripe_payment_intent_id`.
  2. Creates a matching refund reference in Shopify (`POST /admin/api/{version}/orders/{id}/refunds.json` or note/tag sync) and persists `stripe_refund_id` and `shopify_refund_id`.
  3. Updates `orders.payment_status` to `partially_refunded` or `refunded`.

### 2. GDPR Privacy & PII Anonymization (US-23 / S3-T4)
- **Compliance Baseline**: Under the Australian Privacy Principles (APP) and statutory recordkeeping mandates of the Australian Taxation Office (ATO), financial transaction records and invoice totals must be preserved for 5 years.
- **Redaction Protocol**:
  - `customers/data_request`: Returns all stored customer data, orders, and payment records in standard JSON format within statutory response window.
  - `customers/redact`: Anonymizes customer Personal Identifiable Information (PII) by overwriting `first_name`, `last_name`, `phone`, `email` (`redacted_{id}@privacy.local`), and clears any saved Stripe payment method tokens. Orders and payment totals remain intact for accounting integrity.
  - `shop/redact`: Deactivates tenant record 48 hours post-uninstall and purges sensitive tokens.

### 3. Multi-Tenant Administrative Audit Log (US-24 / S3-T5)
- All state-altering administrative actions must create an immutable row in `audit_logs`:
  - Financial actions: Refunds, manual payment link generations.
  - Configuration updates: Plan create/update/delete, Stripe API credentials connect/disconnect.
  - Privacy events: Customer redactions, data export requests.
- Attributes actor (`user_id` or system) and stores contextual diffs in `meta` JSONB.

### 4. Polaris Embedded Dashboard & Filter UI (US-07 / S3-T1 & S3-T2)
- KPI cards for Cash Collected, Balances Due, Upcoming Charges, and Failed Payments rendered dynamically on Overview and Orders views with click-to-filter shortcuts.
- Orders table includes status filter pills (`all`, `deposit_paid`, `balance_due`, `paid`, `refunded`, `partially_refunded`, `payment_failed`) and debounced search.

### 5. Mid-Point Client Demo Seeder (S3-T6)
- Dedicated Artisan seeder `Sprint3DemoSeeder` creates realistic Northwind Ceramics products, customers, and orders across each lifecycle state, ensuring a seamless live walkthrough with Emma Carter.

## Consequences
- Clean separation of concern: financial ledger remains accurate and tamper-evident.
- Zero raw card credentials stored (maintaining strict PCI SAQ-A scope).
- Automated tests verify refund limits, webhook idempotency, and PII anonymization.
