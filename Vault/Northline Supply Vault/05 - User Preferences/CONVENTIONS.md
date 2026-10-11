# Coding & Operational Conventions: Northwind Ceramics Payment App

## Security & Payment Conventions (PCI SAQ-A)
- **Zero Card Data Principle**: Never store, accept, or log raw card PANs or CVVs. All card entry must use Stripe-hosted checkout or Stripe Elements.
- **Secrets Management**: Encrypt tokens (`access_token`, Stripe keys) using AES-256 via Laravel `Crypt`. Never commit keys or log secret values.
- **Webhook HMAC & Signing Secrets**:
  - Shopify webhooks: Compute HMAC-SHA256 with the app client secret and compare with `X-Shopify-Hmac-Sha256` using timing-safe comparison (`hash_equals`).
  - Stripe webhooks: Verify payload using Stripe SDK's `Webhook::constructEvent` with `STRIPE_WEBHOOK_SECRET`.
- **Fast Webhook Acknowledgment**: Return HTTP 200 within 1 second. Save raw payload to `webhook_events` and dispatch queue job.

## Monetary & Rounding Standards
- **Integer Cents**: All monetary amounts in database and calculations are stored as whole integers in cents (e.g. 20000 for $200.00 AUD).
- **Rounding Rule**: When calculating percentage deposits, round to nearest cent:
  `deposit = round(total * percentage / 100)`
  `balance = total - deposit`
  The balance is always strictly `total - deposit` to guarantee zero drift.
- **Currency Isolation**: Strictly mirror the Shopify order currency (`AUD`, `USD`, `GBP`, `EUR`). Never run dynamic currency conversions in the app.

## Idempotency & Concurrency Rules
- **Stripe Calls**: Attach an `Idempotency-Key` header with format `{shop_id}:{order_id}:{schedule_id}:{attempt_number}` on every write operation.
- **Database Locks**: Enforce Redis distributed lock (`Cache::lock('charge:' . $scheduleId, 60)`) on every automated scheduled charge execution.
- **Webhook Idempotency**: Deduplicate on `webhook_events.external_id` (Stripe event ID or Shopify webhook ID). If an event is received twice, return HTTP 200 immediately without re-processing.

## Engineering Style (Ponytail Standards)
- Smallest complete changes that solve the requirement.
- No speculative abstractions or boilerplate.
- Document any pragmatic shortcuts as `shortcut: <the limit>, <when to upgrade>`.
- Non-trivial logic must include automated tests or assert-based self-checks.

## Definition of Done (Client Agreement)
- Code is reviewed in a pull request, even when working as the sole developer.
- All tests pass in CI.
- The change is deployed to staging and verified manually.
- Webhook and payment code has an idempotency test.
- No secrets in the repo or Docker image.
- Docs and decision notes are updated in Obsidian.
- Demonstrated in the sprint demo.

## Working Rhythm
- **Sprint Start**: 1 hour to confirm scope and tasks.
- **Weekly Check-in with Emma**: 30 minutes (short written update sent before call).
- **Sprint End**: Demo on staging, followed by a short retro to document blockers.

