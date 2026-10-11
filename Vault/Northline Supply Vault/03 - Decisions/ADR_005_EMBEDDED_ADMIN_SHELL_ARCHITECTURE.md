# ADR 005: Embedded Admin Shell & Session Token Architecture

## Context
Northwind Ceramics requires a merchant admin dashboard embedded natively inside the Shopify Admin iframe (NFR-16, NFR-17). Following the consolidation onto the Laravel 11 monolith ([[ADR_003_LARAVEL_DOCKER_STACK]]), the embedded admin must provide seamless navigation, Polaris-aligned design, zero CORS issues, and robust session authentication via Shopify App Bridge v4.

## Decision
1. **Entry Point & Presentation Shell**:
   - Serve the embedded admin shell via a dedicated Laravel Blade view (`resources/views/app.blade.php`) at route `/`.
   - Embed Shopify App Bridge v4 via `<meta name="shopify-api-key">`, `<meta name="shopify-shop-domain">`, and official App Bridge CDN script (`https://cdn.shopify.com/shopify-app-bridge/latest/index.js`).
   - Use App Bridge v4 native `<ui-nav-menu>` for sidebar navigation (Overview, Plans, Orders, Settings).
2. **Iframe Security & Framing**:
   - Implement `ShopifyIframeMiddleware` to set `Content-Security-Policy: frame-ancestors https://{shop} https://admin.shopify.com;` dynamically and strip restrictive `X-Frame-Options` headers.
   - Automatically redirect uninstalled or unauthenticated shop visits to `/auth/install?shop={shop}`.
3. **Session Token Verification**:
   - Implement custom, lightweight `VerifyShopifySessionToken` middleware for all `/api/*` merchant endpoints.
   - Cryptographically verify the session token JWT signature using `hash_hmac('sha256')` with `SHOPIFY_API_SECRET` directly using PHP standard libraries (Ponytail principle: zero bloated packages).
   - Verify JWT claims (`dest`, `iss`, `aud`, `exp`, `nbf`), resolve the authenticated `Shop` model, and bind it to `$request`.
4. **Interactive Dashboard Shell**:
   - Provide interactive client-side tab switching and status overviews (Stripe connection status, active plans count, setup checklist) powered by App Bridge toast and title bar actions.

## Status
Accepted / Active

## Date
2026-10-12

## Consequences
- Single container serving both backend API and frontend admin UI without additional Node/Vite processes in production.
- Sub-millisecond JWT authentication without external network requests or third-party package vulnerabilities.
- Native Shopify Admin styling and seamless mobile responsiveness (NFR-17).

## Related
- [[SYSTEM_ARCHITECTURE]]
- [[DESIGN_BRIEF]]
- [[TRD]]
- [[S1_T2_SHOPIFY_INSTALL_FLOW]]
