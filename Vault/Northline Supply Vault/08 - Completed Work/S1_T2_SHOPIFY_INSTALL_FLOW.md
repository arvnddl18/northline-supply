# Completed Work: S1-T2 Shopify Install Flow

## Overview
- **Task ID**: `S1-T2`
- **Title**: Shopify install flow
- **Priority**: `#p0-must`
- **Work Type**: `#dev`
- **Sprint**: Sprint 1
- **Estimate**: 1.5d
- **Status**: Completed

## Objectives Achieved
1. **Shopify OAuth Redirection (`/auth/install`)**:
   - Implemented strict domain validation ensuring only RFC-valid `*.myshopify.com` domains are processed.
   - Built CSRF-resistant state nonce generation and caching.
   - Constructed standard Shopify authorization URL with required scopes (`write_products`, `read_products`, `write_orders`, `read_orders`, `write_customers`, `read_customers`).
2. **OAuth Callback & Token Exchange (`/auth/callback`)**:
   - Cryptographic verification of Shopify's HMAC query parameter signature using `hash_hmac('sha256', ...)`.
   - Verified state nonce from cache to prevent replay/CSRF attacks.
   - Exchanged temporary authorization code for permanent offline access token via Shopify Admin API.
   - Upserted `Shop` record with shop domain, default AUD currency, and installation timestamp.
3. **Encrypted Token Storage at Rest (NFR-3)**:
   - Configured `access_token` attribute on `Shop` model using Eloquent's native `'encrypted'` cast.
   - Access tokens are stored encrypted using AES-256 (`APP_KEY`) in PostgreSQL, ensuring no plaintext credential exposure at rest.
4. **App Uninstalled Webhook Handler (`/webhooks/app/uninstalled` & `/webhooks/shopify`)**:
   - Authenticated webhook requests using `X-Shopify-Hmac-Sha256` signature verification (NFR-2).
   - Exempted webhook routes from CSRF middleware in `bootstrap/app.php`.
   - On `app/uninstalled` event, soft-deactivated shop: cleared `access_token` to null and recorded `uninstalled_at` timestamp.
   - Ensured zero dangling credentials while retaining shop configuration and audit records.
   - Handled re-installation cleanly: resets `uninstalled_at` to null and stores fresh encrypted access token.
   - Returns sub-second response (< 1s) satisfying NFR-11.
5. **Comprehensive Automated Test Coverage**:
   - Created `tests/Feature/ShopifyInstallFlowTest.php` covering:
     - OAuth install redirect to Shopify
     - Invalid shop domain rejection
     - HMAC signature verification and AES-256 encrypted token persistence
     - Tampered HMAC rejection (401)
     - State nonce verification failure (403)
     - Webhook signature validation and clean uninstallation
     - Invalid webhook signature rejection (401)
     - Re-installation clearing uninstalled timestamp and updating token
   - 10 feature tests passing (21 assertions) in 0.35s.

## Related Files
- `app/Models/Shop.php`
- `database/migrations/2026_10_11_000000_create_shops_table.php`
- `app/Services/Shopify/ShopifyOAuthService.php`
- `app/Http/Controllers/Auth/ShopifyAuthController.php`
- `app/Http/Controllers/Webhooks/ShopifyWebhookController.php`
- `routes/web.php`
- `tests/Feature/ShopifyInstallFlowTest.php`
- `Vault/Northline Supply Vault/11 - Specs/PRD.md` (US-01)
- `Vault/Northline Supply Vault/11 - Specs/TRD.md` (Security Architecture & Contracts)
