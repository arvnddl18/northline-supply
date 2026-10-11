---
type: spec
doc: DESIGN_BRIEF
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[APP_FLOW]], [[PRD]]
---

# Design Brief: Northwind Ceramics Payment App

## 1. Design Direction
- **Merchant Admin**: Built strictly with Shopify Polaris Design System and App Bridge v4. Renders seamlessly inside the Shopify Admin iframe to feel like a native core capability of Shopify (NFR-16).
- **Storefront Component**: Embedded via Shopify Theme App Extension (App Block / App Embed). Lightweight (< 30 KB JS, < 100 ms load time) and unobtrusive, adopting the active Shopify theme's font, color tokens, and button styles.
- **Customer Portal**: Minimalist, clean, and mobile-optimized web UI for viewing payment schedules and updating card credentials via Stripe-hosted Elements.
- **Accessibility**: All customer-facing components and portal screens strictly adhere to WCAG 2.1 AA standards (NFR-18).

## 2. Polaris Embedded Admin Standards
| Token / Component | Usage & Style |
|---|---|
| **Page Layout** | `Page` container with breadcrumb navigation and contextual primary action buttons. |
| **Card / Section** | `Card` and `Layout.Section` to organize settings, plan configurations, and metric overviews. |
| **Banner Feedback** | `Banner` (Info, Warning, Critical) used for setup checklists, unpaid order balance warnings, and Stripe dispute notices. |
| **Data Tables** | `IndexTable` with sticky headers, bulk selection, and pagination for order ledgers and plans. |
| **Forms** | `TextField`, `Select`, `RadioButton`, and `ResourcePicker` for product and collection targeting. |
| **Badges** | `Badge` status indicators: `Info` for Deposit Paid, `Warning` for Balance Due / Payment Failed, `Success` for Paid, `Critical` for Dispute. |

## 3. Mobile Admin Experience (NFR-17)
- Emma frequently checks and manages orders from her mobile device.
- The Polaris layout must collapse gracefully into a single-column stack on mobile viewports.
- Touch target sizes for primary actions (resending balance links, viewing payment schedules) must exceed 44x44px.

## 4. Storefront PDP Deposit Widget Design
- Placed directly above or below the native Shopify "Add to Cart" / "Buy Now" button.
- Radio / Pill toggle:
  - Option 1: "Pay in Full — $200.00 AUD"
  - Option 2: "Deposit Option — Pay $60.00 AUD today, $140.00 AUD when shipped"
- Visual breakdown card outlining future scheduled payments and due dates.
- Zero layout shift (CLS < 0.05) and instant DOM hydration (< 100 ms).

## 5. Customer Balance Payment & Portal Screens
- **Hosted Balance Payment Page**:
  - Displays order thumbnail, ceramic product title, order number, and amount due.
  - Secure payment form powered by Stripe Elements.
  - Clear confirmation screen upon settlement; friendly expired/already-paid notice for invalid links.
- **Customer Portal**:
  - Passwordless entry via emailed magic link or direct customer account SSO.
  - Active plans, upcoming billing schedules, transaction history, and self-service card update.
  - Subscription management controls for Ceramic Club: Pause, Skip Month, Cancel.

## Change Log
| Date | Change | Old → New | Reason |
|---|---|---|---|
| 2026-10-11 | Design brief baseline | Scaffold design brief → Northwind Ceramics v1.0 | Full requirements ingestion from client specification |
