# Sprint 2 Completion: Webhooks, Orders, Balance Links, and Reminders

**Date:** 2026-10-12  
**Status:** Completed & Verified (93/93 automated tests passing, 381 assertions)  
**Stories Delivered:** US-05, US-06, US-09, US-18, US-22  
**Related Specs:** [[PRD]], [[TRD]], [[APP_FLOW]], [[DATABASE_SCHEMA]], [[IMPLEMENTATION_PLAN]]  

---

## 1. Summary of Delivered Work

### S2-T1: Order and Payment Database Migrations
- `2026_10_13_000001_create_orders_table.php`: Multi-tenant `orders` table storing integer cents amounts (`total_amount`, `paid_amount`), financial statuses, and indexes on `['shop_id', 'payment_status']` and `shopify_order_id`.
- `2026_10_13_000002_create_payment_schedules_table.php`: Multi-tenant scheduled balances (`amount`, `due_at`, `status`, `next_retry_at`, `attempt_count`).
- `2026_10_13_000003_create_payments_table.php`: Captured charge events linking Stripe `payment_intent` and `schedule_id`.
- `2026_10_13_000004_create_webhook_events_table.php`: PostgreSQL `jsonb` idempotency ledger with unique index on `external_id`.
- `2026_10_13_000005_create_email_templates_table.php`: Merchant-customizable transactional email templates per shop with unique composite `['shop_id', 'key']`.
- `2026_10_13_000006_create_email_logs_table.php`: Delivery history log preventing duplicate customer reminder notifications (NFR-6).
- Eloquent Models: `Order`, `PaymentSchedule`, `Payment`, `WebhookEvent`, `EmailTemplate`, `EmailLog` with reciprocal relationships on `Shop` and `Customer`.

### S2-T2: Webhook Receivers (`/webhooks/stripe` & `/webhooks/shopify`)
- `StripeWebhookService`: Cryptographic HMAC-SHA256 signature verification in native PHP (`hash_equals`) with replay protection (300-second timestamp tolerance) and zero SDK bloat.
- `StripeWebhookController`: Sub-second acknowledgement (< 50ms) and atomic deduplication via `WebhookEvent::firstOrCreate(['external_id' => ...])`. Duplicate deliveries immediately return `HTTP 200 duplicate_ignored`.
- `ShopifyWebhookController`: Enhanced with `WebhookEvent` recording and deduplication.

### S2-T3: Event Jobs (`ProcessStripeWebhookJob`)
- Asynchronous queue worker processing `checkout.session.completed` for both upfront pre-order deposits and subsequent balance settlements.
- Upserts customer records, creates `orders`, logs `payments`, creates 30-day `payment_schedules`, provisions Shopify pre-order via Admin API, executes status writeback, and dispatches confirmation email.
- Guarded against duplicate order creation across mid-job retries.

### S2-T4: Status Writeback
- `ShopifyOrderService::syncPaymentStatus`: Synchronizes order tags (`deposit-paid`, `balance-due` -> `paid`) and Shopify Admin metafields under `northwind_deposit` namespace (`status`, `deposit_cents`, `balance_cents`, `due_at`, `balance_link`).

### S2-T5: Balance Payment Link & Checkout
- Cryptographically signed expiring URLs via `URL::signedRoute('pay.balance', ...)`.
- Customer-facing responsive landing view `resources/views/customer/balance_payment.blade.php`:
  - Gracefully renders clear messages for expired or used links without error exposure.
  - Displays pre-order totals, deposit paid, and balance due.
  - Redirects to hosted Stripe Checkout Session with `balance_settlement` metadata.
  - Webhook settlement transitions order to `paid` and schedule to `succeeded`.

### S2-T6: Scheduler & Reminders (`app:send-balance-reminders`)
- Daily scheduled artisan console command (`Schedule::command('app:send-balance-reminders')->dailyAt('09:00')`).
- Scans `PaymentSchedule` records due within 7 days, checks `email_logs` to ensure no duplicate email is ever sent for the same step (NFR-6), generates signed link, and dispatches reminder.

### S2-T7: Transactional Email Service & Templates
- `EmailService`: Token substitution engine (`{customer_name}`, `{order_number}`, `{deposit_amount}`, `{balance_amount}`, `{due_date}`, `{payment_link}`).
- `OrderTransactionalMail`: Clean, responsive HTML email template for `deposit_received`, `balance_due`, and `balance_paid`.
- `EmailTemplateController`: Embedded admin API allowing merchants to view default templates, customize copy, preview tokens, and trigger test sends.
- `OrderController`: Orders list API with live financial status labels, remaining balance calculation, and manual payment link generator (US-05, US-06).

---

## 2. Test Verification & Security Audit
- **PHPUnit Automated Test Suite**: 93 passed (381 assertions, zero regressions).
- **Frontend Checks**: `npm run typecheck` and `npm run lint` passed cleanly.
- **Security Audit**: Completed by `security-reviewer` subagent: PCI DSS SAQ-A compliant (zero raw PAN/CVVs stored or handled), HMAC signature verification, signed URL tamper protection, AES-256 encrypted credentials at rest.
- **Code Review**: Completed by `php-reviewer` subagent: PSR-12 and Laravel 11 idioms compliant, Ponytail minimalist principles satisfied with tagged shortcuts.
- **Notion Kanban Synchronization**: S2-T1 through S2-T7 marked `Done` and `Verified`.
