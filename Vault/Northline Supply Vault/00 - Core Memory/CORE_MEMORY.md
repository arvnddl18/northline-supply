# Core Memory

## Project
- **Name**: Northwind Ceramics Payment App
- **Client**: Emma Carter, Northwind Ceramics (Melbourne, Australia)
- **Purpose**: Custom Shopify payment app integrating Stripe to collect pre-order deposits, automated scheduled balance payments, installment plans, and recurring Ceramic Club subscriptions for handmade made-to-order ceramics.
- **Current objective**: Sprint 0 environment setup, checkout strategy validation, and living specifications baseline.

## Current State
- **Active feature**: Specification and living memory architecture alignment.
- **Current development phase**: Sprint 0 (Setup, Decisions, and Environments).
- **Important blockers**: None.

## Technology
- **Backend**: Laravel 11, PHP 8.3 (REST API, Webhook handlers, Queue workers, Scheduler for due charges).
- **Admin Frontend**: React with Shopify Polaris, App Bridge (Embedded Admin inside Shopify).
- **Storefront**: Shopify Theme App Extension (PDP & Cart deposit/installment widget).
- **Customer Portal**: Passwordless magic link / Shopify Customer Account integration with Stripe-hosted Elements.
- **Database**: PostgreSQL (Amounts stored as whole numbers in smallest currency unit / cents).
- **Cache & Queues**: Redis (queues, worker supervisor, distributed concurrency locks).
- **Payments**: Stripe (PaymentIntents, SetupIntents, Checkout, Billing/Subscriptions).
- **Transactional Mail**: Postmark or Amazon SES.
- **Infrastructure & Monitoring**: Docker, GitHub Actions CI/CD, VPS, Sentry error tracking, Uptime Kuma.

## Architecture
- **Key architectural decisions**:
  - [[ADR_001_SHOPIFY_REACT_ROUTER_EMBEDDED_STACK]] (Historical template baseline)
  - [[ADR_002_CHECKOUT_STRATEGY]]: Option B Stripe-first checkout via Shopify App Proxy as primary architecture; Option A deposit variant as fallback.
  - Multi-tenant data isolation with `shop_id` scoping on all data models.
  - Sub-second webhook ingestion (save raw payload to `webhook_events`, respond 200, process in queue).
  - Redis distributed locks on schedule rows to prevent duplicate charges.
- **Important modules**:
  - `app/Http/Controllers/Api`: REST API for Admin dashboard and portal.
  - `app/Http/Controllers/Webhooks`: Shopify and Stripe webhook handlers.
  - `app/Jobs`: Async background queue jobs for order sync, retries, and emails.
  - `app/Console/Commands`: Scheduler checking due balances and retry dates.
  - `theme-extension`: Storefront widget (< 30 KB JS, < 100 ms render).

## Permanent Constraints
- **Authority**: Repository code is authoritative for current reality; Obsidian records intent, requirements, architecture, and learnings.
- **Security & Privacy**: Zero raw card data storage on server (PCI SAQ-A scope). Encrypt tokens/keys at rest (AES-256). Verify all webhook HMAC/signatures. No secrets in Obsidian.
- **Business Scope**: Taxes and shipping are left to Shopify; the app never recalculates them. Multi-currency (AUD, USD, GBP, EUR) strictly follows Shopify order currency.

## Important User Preferences
- **Execution Style**: Ponytail principles — minimal complete changes, clean diffs, no superfluous abstractions, concise reporting.
- **Memory Protocol**: Autonomous progressive refinement, stable cross-linked living specs (`[[Link]]`), token-optimized recall.

## Critical Decisions
- [[ADR_002_CHECKOUT_STRATEGY]] — Option B (Stripe-First Checkout via App Proxy) for custom app.
- [[ADR_003_LARAVEL_DOCKER_STACK]] — Monolithic Laravel 11 in Docker Compose (web, worker, scheduler, postgres, redis).

## Active Problems
- None active.

## Spec References
- PRD: [[PRD]] — Active / v1.0
- TRD: [[TRD]] — Active / v1.0
- App Flow: [[APP_FLOW]] — Active / v1.0
- Design Brief: [[DESIGN_BRIEF]] — Active / v1.0
- Database Schema: [[DATABASE_SCHEMA]] — Active / v1.0
- Implementation Plan: [[IMPLEMENTATION_PLAN]] — Active / v1.0

## Important Memory References
- [[OVERVIEW]]
- [[SYSTEM_ARCHITECTURE]]
- [[FUNCTIONAL_REQUIREMENTS]]
- [[CONVENTIONS]]
- [[BACKLOG_RULES]]
- [[CURRENT_STATE]]
- [[DEVELOPMENT_KANBAN]]
- [[CHECKOUT_CHOICE_EMMA_NOTE]]
- [[STRIPE_AND_SHOPIFY_SETUP]]
- [[UPTIME_KUMA_SETUP]]
- [[S0_T6_MONITORING_AND_LOGGING]]

