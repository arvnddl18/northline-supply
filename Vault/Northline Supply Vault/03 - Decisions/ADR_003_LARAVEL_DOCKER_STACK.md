# ADR 003: Laravel 11 Docker Stack Architecture

## Context
Northwind Ceramics Payment App requires a reproducible local development environment and production-grade staging container stack powering the monolithic Laravel 11 backend, queue workers, task scheduler, PostgreSQL 16 database, and Redis cache/queue broker.

## Decision
1. Implement a unified container image (`northwind-payment-app:latest`) based on `php:8.3-fpm-alpine`, packaging:
   - PHP 8.3 with extensions: `pdo_pgsql`, `pgsql`, `bcmath`, `pcntl`, `zip`, `opcache`, `redis` (PECL).
   - Nginx with tuned fastcgi buffers and sub-second proxy pass to local PHP-FPM.
   - Supervisord for managing Nginx and PHP-FPM in the web service.
   - Composer 2.7.
2. Structure Docker Compose (`docker-compose.yml`) into 5 services:
   - `app` (`northwind-payment-web`): Serves HTTP API, Shopify App Proxy, and customer portal on port 8000.
   - `worker` (`northwind-payment-worker`): Asynchronous background queue worker processing Stripe/Shopify jobs (`php artisan queue:work --tries=3 --timeout=90`).
   - `scheduler` (`northwind-payment-scheduler`): Recurring cron runner (`php artisan schedule:work`) checking due balance charges and link expirations.
   - `postgres` (`northwind-postgres`): PostgreSQL 16 on port 5432 with integer-cents schema and health check (`pg_isready`).
   - `redis` (`northwind-redis`): Redis 7 on port 6379 for sub-millisecond queues, cache, and distributed concurrency mutex locks (`Cache::lock`).
3. Standardize developer commands via `Makefile` (`make setup`, `make up`, `make down`, `make test`, `make migrate`) and cross-platform PowerShell runner (`scripts/stack.ps1`).
4. Archive earlier Node.js template routes safely to `legacy-node-scaffold/` while preserving root-level Shopify CLI configurations (`shopify.app.toml`) and Theme App Extensions (`extensions/`).

## Status
Accepted / Active

## Date
2026-10-12

## Consequences
- Single command startup (`make up` or `.\scripts\stack.ps1 up`) brings up the complete containerized stack.
- The web container, worker, and scheduler share identical PHP runtime dependencies without container drift.
- Full parity between local development, GitHub Actions CI testing, and staging VPS deployments.
