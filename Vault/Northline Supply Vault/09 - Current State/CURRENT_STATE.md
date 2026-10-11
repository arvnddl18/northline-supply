# Current Project State: Northwind Ceramics Payment App

## Completed
- Complete 12-page requirements & design specification analyzed and digested.
- **Official 10-page Sprint Plan (Sprint 0 to Sprint 5, v1.0, 50 dev days, 24 stories) ingested and baseline established**:
  - `00 - Core Memory/CORE_MEMORY.md` updated with Northwind Ceramics context and milestone gates.
  - `01 - Project Memory/OVERVIEW.md` updated with client, rhythm, and phasing details.
  - `02 - Architecture/SYSTEM_ARCHITECTURE.md` updated with Laravel + React Polaris + PostgreSQL/Redis topology.
  - `03 - Decisions/ADR_002_CHECKOUT_STRATEGY.md` created (Option B primary, Option A fallback).
  - `04 - Requirements/FUNCTIONAL_REQUIREMENTS.md` updated (FR-1 through FR-10).
  - `05 - User Preferences/CONVENTIONS.md` updated with PCI SAQ-A, integer cents, Definition of Done, and idempotency conventions.
  - `11 - Specs/PRD.md` updated with all 10 FRs, 22 NFRs, and 24 User Stories.
  - `11 - Specs/TRD.md` updated with API contracts, security rules, and tech stack.
  - `11 - Specs/APP_FLOW.md` updated with 7-state payment lifecycle and user flows.
  - `11 - Specs/DESIGN_BRIEF.md` updated with Polaris standards and storefront constraints.
  - `11 - Specs/DATABASE_SCHEMA.md` updated with all 14 database models and ERD.
  - `11 - Specs/IMPLEMENTATION_PLAN.md` fully synchronized to official Sprint Plan v1.0 (Sprints 0 to 5, 50 developer days, deliverables, done-when criteria, testing, risks, 10-point Go-Live Checklist, 5-stage scope cut list).
  - `09 - Current State/DEVELOPMENT_KANBAN.md` populated with all 36 granular sprint tasks (`S0-T1` through `S5-T7`) tagged by sprint and linked to specs.
- **S0-T1 Completed**: Written decision record [[ADR_002_CHECKOUT_STRATEGY]] locked in: Option B (Stripe-First Checkout via App Proxy) confirmed as primary with Option A as emergency fallback. Defer tax/shipping to final balance, initialize Shopify order as `PARTIALLY_PAID`, hybrid balance trigger (estimated date + manual button), and non-refundable deposit terms once making begins.
- **S0-T2 Completed**: Shopify and Stripe test environment provisioned:
  - `shopify.app.toml` updated with required Option B scopes (`write_orders`, `write_customers`) and `[app_proxy]` route.
  - `.env.example` created with complete Shopify and Stripe AUD test configurations.
  - Stripe CLI v1.53.1 installed on host machine via winget.
  - `scripts/stripe-listen.ps1` and npm shortcuts (`npm run stripe:listen`, `npm run stripe:trigger`) established.
  - Complete runbook recorded in [[STRIPE_AND_SHOPIFY_SETUP]].
- **S1-T1 Completed**: Core migrations & multi-tenant models:
  - Multi-tenant `users` table with `shop_id` foreign key, `role` (`owner`/`staff`), and nullable authentication fields for embedded sessions.
  - `plans` table storing configurations for deposits (percentage and fixed integer cents), installments, and subscriptions with `active` query scope.
  - `plan_targets` table associating plans with Shopify product GIDs or collection GIDs with cascade delete.
  - `customers` table mapping Shopify customers to Stripe customer profiles and default payment methods.
  - Models: `Shop`, `User`, `Plan`, `PlanTarget`, `Customer` with mutual relationships.
  - Verified against PostgreSQL 16 container with complete migration rollback and re-apply testing.
  - 5 feature tests in `tests/Feature/CoreMigrationsTest.php` passing cleanly.
  - Full documentation in [[S1_T1_CORE_MIGRATIONS]].
- **S1-T2 Completed**: Shopify install flow & encrypted token storage:
  - OAuth initiation endpoint (`/auth/install`) with domain validation and CSRF state nonce.
  - OAuth callback (`/auth/callback`) with cryptographic HMAC validation and token exchange.
  - `Shop` model with AES-256 access token encryption at rest via Eloquent's encrypted cast (NFR-3).
  - `app/uninstalled` webhook receiver (`/webhooks/app/uninstalled`) with HMAC verification, token zeroing, and soft deactivation.
  - Full automated feature tests in `tests/Feature/ShopifyInstallFlowTest.php` (10 passed, 21 assertions).
  - Detailed documentation in [[S1_T2_SHOPIFY_INSTALL_FLOW]].

- **S0-T5 Completed**: Staging deploy configuration & automated CI/CD pipeline:
  - Containerized Caddy v2 reverse proxy (`docker/Caddyfile`) with automatic Let's Encrypt TLS and parameterized domain routing.
  - Staging environment stack (`docker-compose.staging.yml`) orchestrating Caddy, Laravel 11, background worker, scheduler, PostgreSQL 16, and Redis 7.
  - GitHub Actions CD workflow (`.github/workflows/deploy-staging.yml`) deploying automatically to Ubuntu VPS on merge to `main` via SSH.
  - Automated post-deploy verification: zero-downtime container updates, `php artisan migrate --force`, cache warmup (`config:cache`, `route:cache`), queue restart (`queue:restart`), and healthcheck verification via `/up`.
  - Staging deploy script (`scripts/deploy-staging.sh`) and Makefile targets (`make staging-up`, `make staging-down`, `make staging-deploy`).
  - Full staging runbook documented in [[STAGING_DEPLOYMENT_RUNBOOK]].

- **S0-T4 Completed**: Automated CI pipeline:
  - GitHub Actions multi-job CI workflow (`.github/workflows/ci.yml`) triggered on push and pull requests to `main`.
  - Frontend static verification: ESLint with custom ignore rules and TypeScript typechecking (`tsc --noEmit`).
  - Backend automated testing: PHP 8.3 environment with extensions, composer dependency caching, and full PHPUnit test execution (`php artisan test`).
  - Containerization & publication: Self-contained production Docker image build with buildx cache; pushes tagged images (`sha-<hash>` and `latest`) to GitHub Container Registry (`ghcr.io`) upon merge to `main`.
  - Production Dockerfile packaging: application source bundled, directory permissions pre-configured, and optimized dependencies pre-installed (`--no-dev --optimize-autoloader`).
- **S0-T7 Completed**: Backlog and rules:
  - Definition of Done codified in repository pull request template (`.github/pull_request_template.md`).
  - Developer and AI agent operating guidelines established in `AGENTS.md`.
  - Client-facing checkout decision brief drafted for Emma Carter in [[CHECKOUT_CHOICE_EMMA_NOTE]], linked to [[ADR_002_CHECKOUT_STRATEGY]].
  - Backlog operating rules codified in [[BACKLOG_RULES]] (WIP=1, column transitions, tag taxonomy, 5-stage scope cut hierarchy).
  - Living board synchronized via `scripts/sync-kanban.js`.
  - Full documentation in [[S0_T7_BACKLOG_AND_RULES]].
- **S0-T6 Completed**: Monitoring and logging:
  - Sentry error tracking integrated via `sentry/sentry-laravel` (`^4.29`) with strict PCI SAQ-A sanitization and header scrubbing in `config/sentry.php`.
  - Structured logging configured with Monolog `JsonFormatter` for containerized `stderr` and `json` channels in staging/production (`config/logging.php`).
  - `RequestCorrelationIdMiddleware` assigns shared `request_id` across response headers (`X-Request-ID`), Monolog context, and Sentry event tags.
  - Comprehensive health check endpoint `/health` monitors Database PDO, Redis ping, and Redis queue backlog depth against configurable thresholds.
  - Optional self-hosted Uptime Kuma compose stack (`docker-compose.monitoring.yml`) and runbook documented in [[UPTIME_KUMA_SETUP]].
  - Full documentation in [[S0_T6_MONITORING_AND_LOGGING]].
- **S0-T3 Completed**: Repository and Docker stack:
  - Unified multi-stage Docker container (`northwind-payment-app:latest`) running PHP 8.3-FPM, Nginx, Supervisor, PECL Redis, and PostgreSQL client.
  - 5-service Docker Compose topology (`app` on port 8000, `worker`, `scheduler`, `postgres` on 5432, `redis` on 6379).
  - One-command startup via `Makefile` (`make setup`, `make up`, `make test`, `make migrate`) and cross-platform PowerShell runner (`scripts/stack.ps1`).
  - Standard Laravel 11 root application structure established, with legacy Node template code safely archived to `legacy-node-scaffold/`.
  - Live verified: `/health` returns HTTP 200 with Postgres, Redis, and Queue status green; automated test suite passes.
  - Decision recorded in [[ADR_003_LARAVEL_DOCKER_STACK]]; completed work documented in [[S0_T3_REPOSITORY_DOCKER_STACK]].

## In Progress
- **S1-T3**: Embedded admin shell (App Bridge v4, session tokens, Polaris layout, navigation).

## Blocked
- None.

## Next Logical Tasks
1. Complete S1-T3 embedded admin shell implementation and verification.
2. Advance to S1-T4: Stripe connection.

## Development Kanban
- Living board: [Northwind Ceramics — Development Board (Notion)](https://app.notion.com/p/3f64a48ac6c98056ae2be7af1bf1ff31)
- Database ID: `3f64a48a-c6c9-8056-ae2b-e7af1bf1ff31`
- Parent Page: [To Do List](https://app.notion.com/p/To-Do-List-3f64a48ac6c9800aaaeeee87f7f255be)
- Buddy Mode: Antigravity Development Buddy Mode active (`Section 37`).
- Historical Snapshot: [[DEVELOPMENT_KANBAN]] preserved as markdown baseline.

## Last Updated
2026-10-12

