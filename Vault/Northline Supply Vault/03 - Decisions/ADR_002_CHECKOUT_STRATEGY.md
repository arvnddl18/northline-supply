# ADR-002: How the Deposit Reaches Checkout (Option B vs Option A)

## Context
Shopify checkout does not allow a normal third-party app to serve as an arbitrary payment gateway inside native checkout. Shopify only permits this for approved Shopify Payments Partner Apps. For Northwind Ceramics, Emma Carter requires collecting deposits upfront, automatically charging remaining balances, splitting purchases into installments, and billing monthly subscriptions through Stripe. A checkout mechanism decision is needed early.

## Options Considered

### Option A: Deposit Variant
- **Mechanism**: The app dynamically generates a hidden "Deposit" variant priced at the deposit amount (e.g., $60 AUD on a $200 AUD item). The customer pays this in native Shopify checkout. The full order price and balance are saved on the Shopify order. The balance is billed later via a Stripe payment link.
- **Pros**:
  - Works on any standard Shopify plan out of the box.
  - Retains native Shopify checkout, tax engines, and carrier shipping calculations.
- **Cons**:
  - The initial deposit passes through the shop's existing gateway (Shopify Payments / PayPal), not the app's Stripe flow.
  - Credit cards cannot be saved for automated off-session charging.
  - Customer must manually open and pay balance links; automated installments cannot be charged without human friction.

### Option B: Stripe-First Checkout (via Shopify App Proxy)
- **Mechanism**: The customer clicks the deposit checkout button on the storefront. An app proxy endpoint (`/proxy/checkout`) creates a Stripe Checkout session that charges the deposit amount and saves the card (`setup_future_usage: 'off_session'`). Upon successful completion, the app creates the Shopify order via the Admin API and marks it paid/partially paid.
- **Pros**:
  - All funds flow directly through Stripe into Emma's connected account.
  - Saved customer payment methods allow automated off-session balance collection, automated installment charges, and seamless Ceramic Club subscriptions.
- **Cons**:
  - Taxes and shipping calculation must be retrieved or passed from Shopify via Draft Orders / Admin API.
  - Requires policy compliance checks if ever released as a public app.

## Decision
**Adopt Option B (Stripe-First Checkout)** as the primary architecture for this custom app.
- Emma explicitly requires automated installment charging and subscriptions through Stripe.
- Saved cards are an absolute prerequisite for automated scheduled collections.
- **Fallback provision**: If time or policy constraints require a fast MVP demo, Option A can be demonstrated as an interim fallback while Option B completes end-to-end testing.

## Status
Accepted

## Date
2026-10-11
