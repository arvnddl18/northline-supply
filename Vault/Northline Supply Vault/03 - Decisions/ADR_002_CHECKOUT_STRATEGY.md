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
**Adopt Option B (Stripe-First Checkout)** as the primary architecture for this custom app, with Option A as emergency fallback.

Key architectural determinations agreed in Sprint 0 (S0-T1):
1. **Primary Gateway**: Option B via Shopify App Proxy (`/proxy/checkout`) creating a Stripe Checkout Session with `setup_future_usage: 'off_session'`.
2. **Shipping & Taxes**: Defer to final balance payment. The upfront Stripe checkout charges only the product deposit (e.g. 30%), avoiding complex pre-order cart draft order calculations upfront. Exact Shopify shipping and taxes are attached to the balance invoice when goods are ready for dispatch.
3. **Order Initialization**: Upon receiving Stripe's `checkout.session.completed` webhook, create the Shopify Order via Admin GraphQL API with financial status `PARTIALLY_PAID`, an initial transaction for the deposit amount, and tags `deposit-paid`, `balance-due`.
4. **Balance Collection Mechanism**: Hybrid model—default to an estimated completion date (e.g., 30 days) with automated 7-day reminder emails/links, plus a manual "Request Balance Now" button in Emma's Polaris admin if ceramics are finished early.
5. **Cancellation & Deposit Policy**: Clearly state at checkout that deposits are non-refundable once production begins (standard for handmade made-to-order craft), while preserving Emma's ability to issue discretionary full or partial refunds directly from the embedded admin.
6. **Fallback Provision**: If Shopify API policy constraints or sandboxing delay order creation during Sprint 1 testing, Option A (deposit variant) will serve as interim fallback.

## Status
Accepted & Confirmed

## Date
2026-10-11

