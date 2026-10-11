# Technical Knowledge: Uptime Kuma & Monitoring Setup

## Overview
This runbook defines the operational monitoring and alerting topology for Northwind Ceramics Payment App as required by **NFR-21** and **S0-T6**.

It covers:
1. **Health Check Probes (`/health` & `/up`)**
2. **Uptime Kuma Configuration & Setup**
3. **Queue Backlog & Worker Heartbeat Monitoring**
4. **Sentry Error Tracking & PCI SAQ-A Data Scrubbing**

---

## 1. Health Endpoints Architecture

| Endpoint | Type | Purpose | Frequency | Target Code |
|---|---|---|---|---|
| `/up` | Liveness | Fast container alive check (no heavy DB/Redis queries) | Every 10s | Docker healthcheck (`curl -f http://localhost:80/up`) |
| `/health` | Readiness & Deep Diagnostics | Verifies Database PDO connection, Redis connection, and Queue backlog depth | Every 30s – 60s | Uptime Kuma HTTP Keyword Monitor |

### Sample Healthy Payload (`GET /health`)
```json
{
  "status": "ok",
  "app": "Northwind Ceramics Payment App",
  "environment": "staging",
  "timestamp": "2026-10-12T03:15:42+11:00",
  "services": {
    "database": {
      "connection": "pgsql",
      "status": "ok",
      "error": null
    },
    "redis": {
      "status": "ok",
      "error": null
    },
    "queue": {
      "connection": "redis",
      "status": "ok",
      "size": 0,
      "threshold": 100,
      "error": null
    }
  }
}
```

If the database or Redis fails, `/health` returns HTTP 503 with `"status": "degraded"`.
If the queue size exceeds `QUEUE_BACKLOG_THRESHOLD` (default: 100), `"services.queue.status"` becomes `"warning"`.

---

## 2. Uptime Kuma Setup

### Option A: Self-Hosted Docker Container (Staging/Production VPS)
The project provides an optional `docker-compose.monitoring.yml` definition.

1. **Start Uptime Kuma**:
   ```bash
   make monitoring-up
   # or: docker compose -f docker-compose.monitoring.yml up -d
   ```
2. **Access Dashboard**:
   Navigate to `http://<SERVER_IP>:3001` (or configure Caddy reverse proxy to `kuma.northwindceramics.com.au`).
3. **Initial Admin Account**:
   Create initial administrator username and strong password.

### Option B: External Uptime Kuma Instance
Connect an external monitoring node to the public staging URL.

---

## 3. Creating the Health Monitor in Uptime Kuma

In Uptime Kuma UI:
1. Click **Add New Monitor**.
2. Configure fields:
   - **Monitor Type**: `HTTP(s) - Keyword`
   - **Friendly Name**: `Northwind Payments - Staging API & Queues`
   - **URL**: `https://<STAGING_DOMAIN>/health`
   - **Heartbeat Interval**: `60` seconds
   - **Retries**: `2`
   - **Keyword**: `"status":"ok"`
   - **Accepted Status Codes**: `200`
3. **Notification Channels**:
   - Add notification channel (Telegram, Slack, Discord webhook, or Postmark email) targeting Emma / Lead Developer.

---

## 4. Sentry Error Tracking & PCI Sanitization

### Environment Variables
In `.env` / VPS secret manager:
```env
SENTRY_LARAVEL_DSN="https://<public_key>@o<org_id>.ingest.sentry.io/<project_id>"
SENTRY_ENVIRONMENT="staging" # or "production"
SENTRY_TRACES_SAMPLE_RATE=0.1
```

### Data Scrubbing & PCI Compliance
In compliance with **NFR-1** (PCI SAQ-A scope) and **NFR-3**:
- `send_default_pii` is forced to `false`.
- `config/sentry.php` implements a strict `before_send` scrubber redacting:
  - Headers: `Authorization`, `X-Shopify-Hmac-Sha256`, `X-Shopify-Access-Token`, `Stripe-Signature`.
  - Body keys: `password`, `token`, `access_token`, `secret`, `stripe_secret`, `shopify_api_secret`, `card`, `number`, `cvv`, `exp_month`, `exp_year`.
- Every incoming request receives an `X-Request-ID` correlation ID, which is attached to Sentry event tags and Monolog context.

---

## 5. Structured Logging

- **Staging / Production**: Logs route to `stderr` or `json` formatted via `Monolog\Formatter\JsonFormatter`.
- **Local Development**: Standard single-file logs in `storage/logs/laravel.log`.
- All log records automatically include `request_id` via `RequestCorrelationIdMiddleware`.
