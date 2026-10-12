# Production Go-Live Verification Runbook (10-Point Checklist)

**Client**: Emma Carter, Northwind Ceramics (Melbourne, VIC, Australia)  
**App**: Northwind Ceramics Payment App (Laravel 11 + Shopify App Bridge v4 + Stripe Elements)  
**Target Completion**: Sprint 5 / Phase 5 Completion (S5-T7)  
**Date**: October 12, 2026  

---

## 10-Point Go-Live Verification Audit

| # | Item | Verification Procedure | Verification Status | Notes |
|:---:|---|---|:---:|---|
| **1** | **Stripe Live Credentials** | Live publishable & restricted keys loaded via environment variables / UI Settings. | &check; **VERIFIED** | AES-256 encrypted at rest; `$hidden` from JSON serialization; zero hardcoded keys. |
| **2** | **Webhook Registration** | Live `/webhooks/stripe` and `/webhooks/shopify` registered in developer portals. | &check; **VERIFIED** | HMAC-SHA256 signatures enforced; atomic idempotency keys deduplicate repeats in `webhook_events`. |
| **3** | **App Installation** | OAuth install flow verified on store domain. | &check; **VERIFIED** | Tokens exchanged, encrypted, and frame-ancestors CSP validated in embedded admin shell. |
| **4** | **Alerts Verification** | Sentry error tracking and health checks verified. | &check; **VERIFIED** | Sentry before-send header scrubbing active; `/health` monitors Postgres, Redis, and queue backlog. |
| **5** | **Backup Restoration** | Automated database backup dump and restore test on PostgreSQL 16. | &check; **VERIFIED** | Clean database restores without data loss or foreign key violations. |
| **6** | **Privacy Webhooks** | Mandatory Shopify GDPR endpoints active. | &check; **VERIFIED** | `customers/data_request`, `customers/redact` (anonymizes PII to `redacted_{id}@privacy.local`), and `shop/redact`. |
| **7** | **Release Image & Rollback** | GitHub Container Registry image tagged by commit SHA. | &check; **VERIFIED** | Rollback tested via `docker stop && docker run --image=ghcr.io/...:sha-<prev>`. |
| **8** | **Real-Money Verification** | Low-value order test ($1.00 AUD) through deposit, balance settlement, and refund. | &check; **VERIFIED** | Verified integer-cent precision, fee reconciliation, and zero-penny leakage. |
| **9** | **Email Inbox Audit** | Transactional templates tested in major email clients (Gmail, Outlook, Apple Mail). | &check; **VERIFIED** | Responsive HTML; SPF, DKIM, and DMARC alignment verified on mail domain. |
| **10** | **Client Handover** | Handover manual delivered to Emma Carter; staging demo accepted. | &check; **VERIFIED** | Delivered in [[MERCHANT_HANDOVER_GUIDE]]; staff permissions confirmed. |

---

## Rollback Protocol

If an unexpected critical production incident occurs:
1. **Revert Deployment**:
   ```bash
   docker stop northwind-payment-web
   docker rm northwind-payment-web
   docker run -d --name northwind-payment-web --network northwind-net -p 8000:80 \
     ghcr.io/northwind/payment-app:sha-<previous-commit>
   ```
2. **Revert Migrations (if needed)**:
   ```bash
   docker exec -it northwind-payment-web php artisan migrate:rollback --step=1
   ```
3. **Queue Reset**:
   ```bash
   docker exec -it northwind-payment-web php artisan queue:restart
   ```
