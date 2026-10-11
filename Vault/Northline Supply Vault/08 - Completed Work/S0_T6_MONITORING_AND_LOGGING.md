# Completed Work: S0-T6 Monitoring and Logging

## Overview
- **Task ID**: `S0-T6`
- **Title**: Monitoring and logging
- **Priority**: `#p2-could`
- **Work Type**: `#dev`
- **Sprint**: Sprint 0
- **Estimate**: 0.5d
- **Status**: Completed

## Objectives Achieved
1. **Sentry Error Tracking & Exception Reporting (`sentry/sentry-laravel`)**:
   - Installed `sentry/sentry-laravel` (`^4.29`) and pinned in `composer.json` / `composer.lock`.
   - Registered `Integration::handles($exceptions)` in `bootstrap/app.php`.
   - Built `config/sentry.php` enforcing PCI SAQ-A compliance (`send_default_pii: false`) and a custom `before_send` scrubber redacting sensitive authorization tokens, Shopify HMACs, Stripe signatures, and payment parameters.
2. **Structured Logging (`config/logging.php`)**:
   - Configured `Monolog\Formatter\JsonFormatter` for containerized `stderr` and dedicated `json` channels in staging and production.
   - Maintained clean single/daily human-readable log channels for local development.
   - Created `RequestCorrelationIdMiddleware` assigning `X-Request-ID` across HTTP response headers, Monolog shared log context (`request_id`), and Sentry tags.
3. **Comprehensive Health Check Endpoint (`app/Http/Controllers/HealthController.php`)**:
   - Extended `/health` to evaluate:
     - PostgreSQL database PDO connection (`services.database.status`).
     - Redis connection ping (`services.redis.status`).
     - Redis queue backlog depth against configurable `QUEUE_BACKLOG_THRESHOLD` (`services.queue.size`, `services.queue.threshold`, `services.queue.status`).
   - Returns HTTP 200 with JSON status `ok` or HTTP 503 `degraded` if core dependencies fail.
4. **Uptime Kuma Monitoring Stack (`docker-compose.monitoring.yml`)**:
   - Created optional self-hosted `louislam/uptime-kuma:1` compose definition with persistent data volume and custom port mapping.
   - Added Makefile targets `make monitoring-up` and `make monitoring-down`.
   - Authored complete monitoring runbook in [[UPTIME_KUMA_SETUP]].
5. **Automated Verification**:
   - Expanded `tests/Feature/HealthCheckTest.php` to verify:
     - Health endpoint structure and services breakdown.
     - Root status endpoint.
     - Correlation ID generation and preservation of incoming client `X-Request-ID`.
     - Structured logging channel resolution.
     - Sentry `before_send` data scrubber removing card numbers, tokens, and HMAC headers.
   - All 14 feature test assertions passing green.

## Related Files
- `app/Http/Controllers/HealthController.php`
- `app/Http/Middleware/RequestCorrelationIdMiddleware.php`
- `config/sentry.php`
- `config/logging.php`
- `bootstrap/app.php`
- `composer.json`
- `composer.lock`
- `.env.example`
- `docker-compose.monitoring.yml`
- `docker-compose.staging.yml`
- `Makefile`
- `tests/Feature/HealthCheckTest.php`
- `Vault/Northline Supply Vault/06 - Technical Knowledge/UPTIME_KUMA_SETUP.md`
- `Vault/Northline Supply Vault/11 - Specs/IMPLEMENTATION_PLAN.md` (S0-T6)
- `Vault/Northline Supply Vault/11 - Specs/PRD.md` (NFR-21)
