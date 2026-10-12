# Northwind Ceramics Payment App — Comprehensive API Reference

**Document Version**: 1.0.0  
**Target Environment**: Shopify Embedded Admin, Storefront App Proxy, Customer Portal  
**Date**: October 12, 2026 (S5-T7)  

---

## 1. Authentication & Security Contracts

1. **Embedded Shopify Admin Endpoints (`/api/*`)**:
   - **Header**: `Authorization: Bearer <Shopify-Session-Token-JWT>`
   - **Validation**: Cryptographic HMAC-SHA256 verification against `SHOPIFY_API_SECRET`.
   - **Claims Required**: Valid `iss`, `dest`, `aud`, expiration within window, matches active `Shop`.
   - **RBAC**: Protected endpoints require Store Owner role (`EnsureOwnerRole` middleware).
2. **Shopify Storefront App Proxy (`/proxy/*`)**:
   - **Query Parameters**: Validated using Shopify HMAC-SHA256 signature verification (`VerifyShopifyAppProxy` middleware).
3. **Customer Portal Endpoints (`/portal/*` & `/pay/*`)**:
   - **Security**: Cryptographically signed URLs (`URL::signedRoute`) with strict TTLs (15 minutes for customer portal, 7 days for balance payment links).
4. **Webhooks (`/webhooks/*`)**:
   - **Shopify**: Cryptographic HMAC header `X-Shopify-Hmac-Sha256`.
   - **Stripe**: Cryptographic signature header `Stripe-Signature` via webhook secret. Atomic idempotency deduplication via `webhook_events` table.

---

## 2. Embedded Admin API Endpoints (`/api/*`)

### Overview & Settings
- `GET /api/overview`: Merchant onboarding checklist, installation status, Stripe connection status, and current `user_role` (`owner` or `staff`).
- `GET /api/dashboard`: Financial KPIs in integer cents (`total_collected_cents`, `pending_balance_due_cents`, `upcoming_charges_count`, `failed_payments_count`).
- `GET /api/stripe/status`: Connection state, publishable key prefix, mode (`test`/`live`).
- `POST /api/stripe/connect` *(Owner Only)*: Securely save AES-256 encrypted Stripe publishable and restricted keys.
- `POST /api/stripe/disconnect` *(Owner Only)*: Zero out Stripe keys and reset integration.

### Plans & Calculations
- `GET /api/plans`: List active and inactive payment plans for the store.
- `POST /api/plans`: Create new plan (deposit percentage, fixed deposit, installment splits, or subscription).
- `GET /api/plans/{id}`: View plan details and associated product/collection GID targets.
- `PUT /api/plans/{id}`: Update plan details and target associations.
- `DELETE /api/plans/{id}`: Delete plan (cascades targets).
- `POST /api/plans/preview`: Live zero-penny calculation breakdown for sample total amounts.

### Orders & Refunds
- `GET /api/orders`: Paginated orders with payment statuses (`deposit_paid`, `paid`, `installment_active`, `failed`, `refunded`, `disputed`) and search query filtering.
- `GET /api/orders/{id}`: Detailed order view with associated customer, payment schedules, and Stripe payment intents.
- `POST /api/orders/{id}/payment-link`: Generate a 7-day cryptographically signed manual balance payment link.
- `POST /api/orders/{id}/refund` *(Owner Only)*: Full or partial refund to Stripe and Shopify. Cancels pending balance schedules on full deposit refund.

### Subscriptions Management (US-12, US-21, S5-T1, S5-T2, S5-T4)
- `GET /api/subscriptions`: Paginated shop memberships with status filtering (`active`, `paused`, `cancelled`).
- `GET /api/subscriptions/{id}`: View membership record with plan and customer.
- `POST /api/subscriptions/{id}/pause`: Pause subscription until specified date or for 1 month. Voids collection on Stripe.
- `POST /api/subscriptions/{id}/resume`: Reactivates paused subscription immediately.
- `POST /api/subscriptions/{id}/skip`: Advances `next_renewal_at` by 1 full billing cycle without charging.
- `POST /api/subscriptions/{id}/change-tier`: Switches membership plan tier with real-time Stripe proration (`create_prorations`), integer cents delta audit logging, and plan record update.
- `POST /api/subscriptions/{id}/cancel` *(Owner Only)*: Cancels recurring subscription on Stripe and marks record cancelled. Blocked (HTTP 403) for staff accounts.

### Transactions & Auditing
- `GET /api/transactions/export` *(Owner Only)*: Memory-bounded streaming CSV export of all payments and refunds with date and status filters.
- `GET /api/audit-logs`: Immutable administrative audit trail for compliance and tracking.

---

## 3. Storefront App Proxy Endpoints (`/proxy/*`)

- `GET /proxy/plan-info`: Resolves applicable deposit or installment plan for a given product or collection ID. Returns calculation breakdown in integer cents.
- `GET /proxy/checkout` & `POST /proxy/checkout`: Initiates Stripe Checkout session with off-session card saving (Option B). Attaches `shop_id`, `plan_id`, and integer-cents breakdown metadata.

---

## 4. Customer Self-Service Portal (`/portal/*`)

- `POST /portal/request-link`: Submits customer email. Generates and returns/emails a 15-minute signed magic link (`customer.portal`). Enumeration-safe.
- `GET /portal/{customer}`: Customer dashboard displaying pre-orders, pending balance links, and Ceramic Club memberships. Protected by `hasValidSignature()`.
- `POST /portal/{customer}/subscriptions/{subscription}/pause`: Customer-initiated pause (1 or 2 months).
- `POST /portal/{customer}/subscriptions/{subscription}/resume`: Customer-initiated resume.
- `POST /portal/{customer}/subscriptions/{subscription}/skip`: Skip next renewal cycle.
- `POST /portal/{customer}/subscriptions/{subscription}/change-tier`: Customer-initiated plan switch with Stripe proration.
- `POST /portal/{customer}/subscriptions/{subscription}/cancel`: Customer-initiated cancellation with confirmation.

---

## 5. Webhooks

- `POST /webhooks/stripe`: Stripe events: `checkout.session.completed`, `invoice.payment_succeeded`, `payment_intent.payment_failed`, `charge.dispute.created`.
- `POST /webhooks/shopify`: Shopify events: `orders/create`, `app/uninstalled`.
- `POST /webhooks/shopify/customers/data_request`: Mandatory GDPR customer data export.
- `POST /webhooks/shopify/customers/redact`: Anonymizes customer PII to `redacted_{id}@privacy.local`.
- `POST /webhooks/shopify/shop/redact`: Deactivates shop data upon store closure.
