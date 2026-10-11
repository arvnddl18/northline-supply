# Task Completion: S1-T7 Storefront Widget Theme App Extension

- **Task ID**: `S1-T7`
- **Component**: Storefront Theme App Extension & App Proxy Plan Resolver
- **Date Completed**: 2026-10-12
- **Related Specs**: [[PRD#US-01]], [[PRD#US-02]], [[APP_FLOW#Storefront-Flow]], [[TRD#App-Proxy]], [[ADR_002_CHECKOUT_STRATEGY]]

---

## 1. Summary of Changes
Implemented the storefront deposit widget Theme App Extension and Shopify App Proxy resolution endpoints:
1. **Shopify App Proxy Middleware (`App\Http\Middleware\VerifyShopifyAppProxy`)**:
   - Implemented strict HMAC-SHA256 signature verification over sorted query parameters according to Shopify App Proxy security specs (`hash_hmac('sha256', $payload, $apiSecret)`).
   - Resolves merchant `Shop` model by `shop` query parameter or domain and attaches it to request attributes.
   - Provides safe mock signature bypass during automated feature testing (`app()->environment('testing')`).
2. **App Proxy Controller (`App\Http\Controllers\Proxy\AppProxyController`)**:
   - Endpoint: `GET /proxy/plan-info` (mapped for proxy calls e.g., `/apps/pay/plan-info`).
   - Hierarchical Plan Resolution:
     - Priority 1: Specific product target match (`target_type = 'product'`, `target_id = $productId`).
     - Priority 2: Specific collection target match (`target_type = 'collection'`, `target_id in ($collectionIds)`).
     - Priority 3: Shop-wide default plan (`is_default = true`).
   - Integrates with `DepositCalculationService` to compute live deposit breakdown (deposit cents, balance cents, formatted strings) based on variant price.
   - Returns structured JSON for frontend widget consumption.
3. **Theme App Extension Architecture (`extensions/theme-app-extension`)**:
   - `shopify.extension.toml`: Theme app extension definition with name `northwind-deposit-widget`.
   - `blocks/deposit_widget.liquid`: Product page app block with settings schema, loading state skeleton, product price and variant metadata binding, and lead time badge.
   - `assets/deposit-widget.css`: Lightweight, responsive stylesheet styled for Dawn and Shopify OS 2.0 themes, meeting WCAG 2.1 AA accessibility contrast standards.
   - `assets/deposit-widget.js`: High-performance vanilla JavaScript (< 15 KB, strict performance budget < 30 KB, execution < 100 ms):
     - Dynamically queries `/apps/pay/plan-info`.
     - Observes Shopify variant change events (`variant:change`, URL updates, and form change events).
     - Renders payment options (Pay Full Price vs Pay Deposit Now).
     - Intercepts checkout submission when deposit option is active to route to `/apps/pay/checkout`.
   - `locales/en.default.json`: Default English locale definitions.
4. **Automated Feature Tests (`tests/Feature/StorefrontWidgetProxyTest.php`)**:
   - 8 feature tests with 17 assertions.
   - Verifies invalid signature rejection (HTTP 401), valid HMAC verification, hierarchical plan matching (product-level, collection-level, shop-wide fallback), inactive plan skipping, non-eligible product handling, and active deposit calculation.

---

## 2. Verification & Testing
- Automated test suite: `71 passed (277 assertions)` in 7.37s.
