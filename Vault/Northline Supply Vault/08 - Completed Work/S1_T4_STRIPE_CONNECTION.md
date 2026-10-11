# Task Completion: S1-T4 Stripe Connection UI and API Keys Setup

- **Task ID**: `S1-T4`
- **Component**: Merchant Settings & Stripe Connection
- **Date Completed**: 2026-10-12
- **Related Specs**: [[PRD#US-02]], [[TRD#Integrations]], [[STRIPE_AND_SHOPIFY_SETUP]]

---

## 1. Summary of Changes
Implemented merchant Stripe account connectivity for Emma Carter's Northwind Ceramics custom application:
1. **Database Schema & AES-256 Encryption at Rest**:
   - Migration `2026_10_12_000005_add_stripe_keys_to_shops_table.php` adding `stripe_publishable_key` and `stripe_secret_key` text columns.
   - `App\Models\Shop` with Eloquent `'encrypted'` casts (NFR-3) ensuring ciphertext storage in PostgreSQL.
   - Guarded `$hidden = ['access_token', 'stripe_secret_key']` preventing serialization leaks.
   - Seamless fallback methods (`getStripePublishableKey()`, `getStripeSecretKey()`) defaulting to environment variables when per-merchant keys are unpopulated.
2. **Service Layer (`StripeConnectService`)**:
   - Validates key format prefixes (`pk_test_`, `pk_live_`, `sk_test_`, `sk_live_`, `rk_test_`, `rk_live_`).
   - Live credential ping via `https://api.stripe.com/v1/account` using native Laravel HTTP client with zero new dependencies.
   - Extracts account ID, currency, charges capability, and test mode status.
   - Key masking (`maskKey()`) for safe UI rendering (e.g., `pk_test_...3a2b`).
3. **API Endpoints & Controller (`StripeConnectionController`)**:
   - `GET /api/stripe/status` — Reports connection state, account ID, test mode, and masked keys.
   - `POST /api/stripe/connect` — Validates, tests live ping, encrypts credentials, and updates merchant connection.
   - `POST /api/stripe/disconnect` — Clears keys and resets account ID.
   - Fully protected under `VerifyShopifySessionToken` App Bridge HS256 JWT middleware.
4. **Polaris Blade UI**:
   - Integrated into `resources/views/app.blade.php` under the Settings tab with live status badge, test mode pill, password input with masking, and instant connection test trigger.
   - Real-time reactivity updating the main Onboarding Checklist without page reloads.
5. **Automated Feature Tests**:
   - 8 comprehensive test cases in `tests/Feature/StripeConnectionTest.php` verifying unauthorized rejection, validation, live Stripe rejection handling, encrypted DB storage assertions, and disconnection.

---

## 2. Verification & Testing
- Automated test suite: `36 passed (140 assertions)`.
- Security audit by `security-reviewer`: PASS (Zero raw PAN/CVVs, PCI SAQ-A scope maintained).
