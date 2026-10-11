# Completed Work: S1-T3 Embedded Admin Shell

## Overview
- **Task ID**: `S1-T3`
- **Title**: Embedded admin shell
- **Priority**: `#p0-must`
- **Work Type**: `#dev`
- **Sprint**: Sprint 1
- **Estimate**: 1.5d
- **Status**: Completed

## Objectives Achieved
1. **Presentation & Entry Shell (`resources/views/app.blade.php`)**:
   - Built responsive embedded merchant dashboard honoring Shopify Polaris design standards (NFR-16, NFR-17).
   - Integrated Shopify App Bridge v4 via `<meta name="shopify-api-key">`, `<meta name="shopify-shop-domain">`, and official CDN scripts.
   - Built native `<ui-nav-menu>` sidebar integration with routes for Overview, Payment Plans, Orders, and Settings.
   - Mobile-optimized Polaris Card layout with touch targets strictly exceeding 44px (NFR-17, WCAG 2.1 AA).
   - Dynamic onboarding checklist reflecting live installation status, Stripe connection, and configured plans.

2. **Iframe Security & Clickjacking Protection (`ShopifyIframeMiddleware`)**:
   - Dynamically calculates and sets `Content-Security-Policy: frame-ancestors https://admin.shopify.com https://{shop};`.
   - Strips restrictive `X-Frame-Options` headers to allow seamless Shopify Admin embedding.
   - Redirects uninstalled shops or invalid sessions to the OAuth install flow (`/auth/install?shop={shop}`) automatically.

3. **Session Token JWT Verification (`ShopifyOAuthService` & `VerifyShopifySessionToken`)**:
   - Cryptographically verifies Shopify App Bridge v4 Session Tokens (RFC 7519 JWT) using HMAC-SHA256 with the app client secret (`SHOPIFY_API_SECRET`).
   - Implemented purely with PHP standard library (`hash_hmac`, `hash_equals`, `json_decode`, `base64_decode`), avoiding third-party package dependencies (Ponytail principle).
   - Verifies claims: `dest`, `iss`, `aud`, `exp` (with 5-second clock skew tolerance), and `nbf`.
   - Resolves authenticated `Shop` model and binds it to the incoming request.

4. **Merchant Overview API (`GET /api/overview`)**:
   - Secured by `VerifyShopifySessionToken` middleware.
   - Returns real-time status of the shop, plan counts, and setup checklist.

5. **Automated Test Suite (`tests/Feature/EmbeddedAdminShellTest.php`)**:
   - 9 feature tests covering:
     - Online JSON response when no shop parameter is provided
     - Rejection of invalid shop domain formats (400)
     - Redirection of uninstalled shops to OAuth install flow (302)
     - Embedded admin view rendering with CSP headers for installed shops (200)
     - Rejection of unauthenticated requests without session tokens (401)
     - Rejection of tampered signatures (401)
     - Rejection of expired session tokens (401)
     - Rejection of sessions for unknown/uninstalled shops (403)
     - Successful overview retrieval with checklist status for valid tokens (200)

6. **Pure Docker Development Topology**:
   - Transitioned local Docker workflows to pure Docker CLI (`docker run`, `docker exec`) via `scripts/stack.ps1` and `npm run stack:*`.

## Verification Results
- `npm run stack:test`: 28 passed, 107 assertions (0 failures)
- `npm run typecheck`: Passed (0 errors)
- `npm run lint`: Passed (0 errors)
- Live check: `http://localhost:8000/health` returns `status: ok` with PostgreSQL and Redis connections healthy.

## Related Files
- `app/Services/Shopify/ShopifyOAuthService.php`
- `app/Http/Middleware/VerifyShopifySessionToken.php`
- `app/Http/Middleware/ShopifyIframeMiddleware.php`
- `app/Http/Controllers/Admin/EmbeddedAdminController.php`
- `app/Http/Controllers/Api/AdminOverviewController.php`
- `resources/views/app.blade.php`
- `routes/web.php`
- `routes/api.php`
- `tests/Feature/EmbeddedAdminShellTest.php`
- `scripts/stack.ps1`
- `Vault/Northline Supply Vault/03 - Decisions/ADR_005_EMBEDDED_ADMIN_SHELL_ARCHITECTURE.md`
