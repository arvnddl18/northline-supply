---
type: spec
doc: TRD
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[PRD]], [[DATABASE_SCHEMA]], [[SYSTEM_ARCHITECTURE]], [[IMPLEMENTATION_PLAN]]
---

# Technical Requirements Document: Northwind Ceramics Payment App

## 1. Tech Stack
- **Backend API & Processing**: Laravel 11, PHP 8.3 (Monolithic API, Queue Workers, Task Scheduler).
- **Embedded Admin Frontend**: React with Shopify Polaris and App Bridge v4.
- **Storefront Integration**: Shopify Theme App Extension (Liquid App Block / Embed, vanilla JS < 30 KB).
- **Customer Portal**: Passwordless magic-link web interface with Stripe Elements.
- **Database**: PostgreSQL (Amounts stored as integer cents; multi-tenant isolation via `shop_id`).
- **Cache & Message Broker**: Redis (Queue processing, distributed concurrency locks, rate limits).
- **Payment Processing**: Stripe API (PaymentIntents, SetupIntents, Checkout Sessions, Billing).
- **Transactional Email**: Postmark or Amazon SES.
- **Hosting & Infrastructure**: Docker containerization, GitHub Actions CI/CD, Ubuntu VPS.
- **Monitoring & Alerting**: Sentry (error tracking), Uptime Kuma (heartbeat & queue monitoring).

## 2. Architecture & Components
- **Architecture Style**: Monolithic service with decoupled queue workers and cron scheduler.
- **Entry Points**:
  - Embedded Admin: Loaded via Shopify Admin iframe with App Bridge session tokens.
  - Webhooks: `/webhooks/shopify` and `/webhooks/stripe` endpoints for real-time asynchronous event ingestion.
  - Storefront: Theme App Extension on product detail pages and cart communicating via `/proxy/checkout`.
- **Data Flow**:
  1. Storefront triggers `/proxy/checkout` via Shopify App Proxy.
  2. Laravel creates a Stripe Checkout session (`setup_future_usage: 'off_session'`) and returns session URL.
  3. Stripe sends `checkout.session.completed` to `/webhooks/stripe`.
  4. Webhook handler validates signature, writes raw event to `webhook_events`, returns HTTP 200 (< 1s), and queues `CreateShopifyOrderJob`.
  5. Worker creates Shopify order via Admin GraphQL API, writes tags/metafields, and creates `payment_schedules` records in PostgreSQL.
  6. Scheduler scans `payment_schedules` every minute, sending balance link emails (7 days before due date) or charging saved cards off-session on due date.

## 3. API & Webhook Endpoints Matrix

| Method | Path | Purpose | Authentication | Payload / Parameters |
|---|---|---|---|---|
| `GET` | `/auth/install` | Shopify OAuth installation initiator | Shopify HMAC | `shop`, `timestamp`, `hmac` |
| `GET` | `/auth/callback` | Shopify OAuth callback & token encryption | Shopify HMAC | `code`, `shop`, `state`, `hmac` |
| `POST` | `/api/stripe/connect` | Connect merchant Stripe account | Polaris Session JWT (Owner) | `stripe_account_id` or API keys |
| `GET` | `/api/plans` | List configured payment plans | Polaris Session JWT | Filter by `type`, `active` |
| `POST` | `/api/plans` | Create a deposit or installment plan | Polaris Session JWT (Owner) | `name`, `type`, `deposit_type`, `deposit_value`, `plan_targets` |
| `PUT` | `/api/plans/{id}` | Edit an existing plan | Polaris Session JWT (Owner) | Updated plan fields |
| `DELETE`| `/api/plans/{id}` | Deactivate/remove a plan | Polaris Session JWT (Owner) | Plan ID |
| `GET` | `/api/orders` | List orders with payment status | Polaris Session JWT | Filters: `payment_status`, pagination |
| `GET` | `/api/orders/{id}` | Detailed order and payment schedule view | Polaris Session JWT | Order ID |
| `POST` | `/api/orders/{id}/refund` | Issue full or partial refund | Polaris Session JWT (Owner) | `amount`, `reason` |
| `POST` | `/api/orders/{id}/payment-link` | Regenerate and email balance link | Polaris Session JWT | Order ID |
| `GET` | `/api/dashboard` | Aggregated financial KPI metrics | Polaris Session JWT | Total collected, pending, overdue |
| `GET` | `/api/transactions/export` | Export transaction ledger to CSV (Phase 2) | Polaris Session JWT (Owner) | Date range, status |
| `POST` | `/proxy/checkout` | Create Stripe Checkout session (app proxy) | Shopify App Proxy signature | `product_id`, `variant_id`, `plan_id` |
| `GET` | `/portal/*` | Customer portal view for schedule & cards | Signed Magic Link / Token | Magic link token |
| `PUT` | `/portal/card` | Update stored card details via SetupIntent | Customer Portal Session | `payment_method_id` |
| `POST` | `/webhooks/shopify` | Ingest orders, refunds, uninstall, GDPR | Shopify HMAC signature | Raw JSON payload |
| `POST` | `/webhooks/stripe` | Ingest PaymentIntents, invoices, disputes | Stripe signing secret | Raw JSON payload |

## 4. Security & Reliability Architecture
- **PCI DSS Scope**: Enforce SAQ-A scope. Zero card PAN or CVV touches Laravel servers; Stripe Elements and hosted Checkout handle all card collection (NFR-1).
- **HMAC & Signature Verification**:
  - Shopify webhooks: Reject payloads where HMAC does not match `hash_hmac('sha256', $rawBody, $clientSecret, true)` (NFR-2).
  - Stripe webhooks: Verify payloads with `\Stripe\Webhook::constructEvent` using `STRIPE_WEBHOOK_SECRET` (NFR-2).
- **Encryption at Rest**: All access tokens and Stripe keys encrypted with AES-256 via Laravel `Crypt` (NFR-3).
- **Payment Idempotency**: Stripe write calls must send `Idempotency-Key: {shop_id}:{order_id}:{schedule_id}:{attempt}` (NFR-5).
- **Webhook Idempotency & DLQ**: Store `external_id` with unique constraint in `webhook_events`. Duplicates return HTTP 200 immediately without side effects. Failures retry up to 5 times before routing to DLQ (NFR-6).
- **Concurrency Locks**: Acquire Redis mutex lock (`Cache::lock('charge:' . $scheduleId, 60)`) before initiating an off-session charge to prevent dual charges (NFR-5, NFR-7).
- **Sub-Second Webhook Response**: Save raw payload, dispatch queue job, return HTTP 200 in under 1 second (< 5s Shopify timeout) (NFR-11).
- **Pre-Fulfillment Safeguard**: Shopify Admin GraphQL pre-fulfillment checks and order metafields warning the merchant if an unpaid balance remains (FR-5.3).

## 5. Performance & Scalability Standards
- **Storefront JS Footprint**: < 30 KB minified/gzipped; < 100 ms added render execution (NFR-9).
- **Dashboard Response Time**: Under 2.0 seconds with datasets up to 10,000 orders using indexed database queries and cursor/limit pagination (NFR-10).
- **Multi-Tenancy**: Every database table is indexed and filtered by `shop_id` (NFR-12).

## 6. Testing Strategy
- **Unit Tests**: Deposit calculation math (rounding to nearest cent, total = deposit + balance), schedule generation dates, state machine transitions.
- **Feature Tests**: Webhook endpoints with saved sample Stripe and Shopify payloads; repeat-delivery tests to verify idempotency.
- **Stripe Test Suite**: End-to-end payment simulations with Stripe test card numbers (success, card decline, 3D Secure authentication).

## Change Log
| Date | Change | Old → New | Reason |
|---|---|---|---|
| 2026-10-11 | Technical specification baseline | Scaffold spec → Northwind Ceramics TRD v1.0 | Full specification ingestion from client document |
