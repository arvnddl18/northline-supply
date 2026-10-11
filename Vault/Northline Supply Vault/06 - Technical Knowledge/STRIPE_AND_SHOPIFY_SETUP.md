---
type: technical-knowledge
status: Active
last_updated: 2026-10-11
confidence: VERIFIED
related: [[IMPLEMENTATION_PLAN]], [[TRD]], [[ADR_002_CHECKOUT_STRATEGY]], [[CORE_MEMORY]]
---

# Technical Knowledge: Shopify and Stripe Test Environment (S0-T2)

## 1. Overview
This runbook defines the development and test account environment for Northwind Ceramics (`S0-T2`), establishing local webhook ingress, partner app permissions, and Stripe AUD test configurations.

## 2. Shopify Test Setup
- **App Type**: Custom / Embedded App (`embedded = true`)
- **API Version**: `2027-01`
- **Client ID**: `8579eb7c49b7d014a15598a7a90b0a2d` (configured in `shopify.app.toml`)
- **Scopes Configured**:
  - Products: `write_products`, `read_products`
  - Orders: `write_orders`, `read_orders` (Required for post-checkout order creation)
  - Customers: `write_customers`, `read_customers`
  - Metaobjects: `write_metaobjects`, `write_metaobject_definitions`
- **App Proxy Configuration** (`ADR-002: Option B`):
  - Prefix: `apps`
  - Subpath: `pay` (accessible at `https://{shop-domain}/apps/pay/...`)
  - Target URL: `https://{app-host}/proxy/checkout`
- **Linking Command**:
  ```bash
  shopify app config link
  ```
  Run to link this repository configuration to your Partner Development Store.

## 3. Stripe Test Setup
- **Account Model**: Direct Stripe test account for Emma Carter / Northwind Ceramics (Melbourne, AU).
- **Default Currency**: `aud` (Australian Dollar; amounts handled in integer cents).
- **Key Storage**: Configured via local `.env` (derived from `.env.example`).
  - `STRIPE_KEY`: Publishable key (`pk_test_...`)
  - `STRIPE_SECRET`: Secret key (`sk_test_...`)
  - `STRIPE_WEBHOOK_SECRET`: Signing secret (`whsec_...`)

## 4. Stripe CLI & Local Webhook Tunneling
- **Binary Status**: Installed natively on Windows (`v1.53.1` via winget in `$LOCALAPPDATA\Microsoft\WinGet\Packages\Stripe.StripeCli...`).
- **Target Endpoint**: `http://localhost:8000/webhooks/stripe`
- **Subscribed Events**:
  - `checkout.session.completed` (Deposit / balance checkout completion)
  - `payment_intent.succeeded` (Off-session scheduled balance & installment charge)
  - `payment_intent.payment_failed` (Card decline / payment failure)
  - `charge.refunded` (Discretionary merchant refund)
  - `customer.subscription.created`, `customer.subscription.updated`, `customer.subscription.deleted` (Ceramic Club)

### Running the Webhook Listener
Execute via npm script shortcut:
```bash
npm run stripe:listen
```
Or directly in PowerShell:
```powershell
.\scripts\stripe-listen.ps1
```
When started, the CLI displays your ephemeral webhook signing secret:
`> Ready! Your webhook signing secret is whsec_xxx`  
Copy this secret into `STRIPE_WEBHOOK_SECRET` in your `.env` file.

### Triggering Test Webhook Events
To test local ingestion:
```bash
npm run stripe:trigger checkout.session.completed
```
