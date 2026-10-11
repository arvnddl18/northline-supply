# Task Completion: S1-T8 Start Checkout (App Proxy)

- **Date**: 2026-10-12
- **Specs**: [[ADR_002_CHECKOUT_STRATEGY]], [[PRD#US-17]]

## Summary
- `GET|POST /proxy/checkout` (HMAC-verified) → `AppProxyController::startCheckout`.
- Validates params, resolves active plan scoped to shop, computes deposit via `DepositCalculationService` (integer cents).
- Creates Stripe Checkout session (`mode=payment`, `setup_future_usage=off_session`, deposit line item, metadata incl. shop/plan/product/variant/qty/total/deposit/balance/currency) and 302-redirects (JSON `redirect_url` when requested).
- Errors: 404 plan, 409 no Stripe keys, 422 invalid amounts, 502 Stripe failure.
- Widget JS now passes `price` and `currency`.
- `shortcut:` price trusted from signed proxy query; re-verify against Shopify Admin API when S2 order creation lands.
- Tests: `StorefrontCheckoutTest` (7). Suite: 78 passed.
