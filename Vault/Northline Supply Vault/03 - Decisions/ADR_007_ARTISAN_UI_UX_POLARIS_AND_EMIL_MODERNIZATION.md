---
type: decision
id: ADR_007
title: Artisan UI/UX Modernization with Shopify Polaris, Emil Kowalski Kinematics, and Component Modularization
status: Accepted
date: 2026-10-12
deciders:
  - Emma Carter (Merchant Owner)
  - Solo Lead Developer
consulted:
  - Antigravity AI Agent
related:
  - "[[ADR_005_EMBEDDED_ADMIN_SHELL_ARCHITECTURE]]"
  - "[[DESIGN_BRIEF]]"
  - "[[APP_FLOW]]"
---

# ADR 007: Artisan UI/UX Modernization (Polaris + Emil Kowalski Design Engineering)

## Context
The Northwind Ceramics application operates two key surfaces:
1. The Merchant Embedded Admin running within the Shopify Admin iframe.
2. The Customer Facing Self-Service Portal, Balance Payment, and Card Update screens.

Previously, `resources/views/app.blade.php` was a monolithic 1,919-line template. Key workflows like creating a deposit plan were rendered as an inline card appended to the bottom of the page, orders had no slide-over detail view to inspect multi-stage payment schedules, there was no skeleton loading during async data fetches, and customer portal actions were cramped inline table buttons using native browser `confirm()` prompts.

## Decision
1. **Design System & Palette**: Adopt an Australian Stoneware & Terracotta artisan aesthetic. Use Bisque cream (`#FAF7F5`), rich Byron Bay terracotta (`#C46238`), celadon sage (`#4A6B53`), and warm charcoal (`#1C1917`) paired with `Playfair Display` editorial serif headers and `Inter` sans-serif body copy.
2. **Kinematics & Micro-Interactions (Emil Kowalski Standards)**:
   - All interactive buttons and cards use tactile press feedback: `transform: scale(0.97)` on `:active` with 140ms ease-out.
   - Modals enter with subtle scale & translate: `scale(0.96) translateY(8px) -> scale(1) translateY(0)` with 260ms drawer curve.
   - Slide-over Drawers (Sheets) enter from the right with `translateX(100%) -> translateX(0)` using `--ease-drawer: cubic-bezier(0.32, 0.72, 0, 1)`.
   - Modals and drawers include backdrop blur (`backdrop-filter: blur(8px)`), accessible focus trapping, ESC key dismiss, and aria-modal attributes.
   - Shimmer skeleton loaders matching table and KPI card geometries are rendered while fetching API data, preventing Cumulative Layout Shift (CLS).
3. **Workflow Elevation**:
   - **Plan Builder**: Extracted into a dedicated Slide-over Drawer with live calculation preview.
   - **Order Schedule Inspector**: Implemented as a rich Slide-over Drawer showing order timelines, payment schedules, retry attempts, Stripe transactions, and email logs.
   - **Modal Dialogs**: Reserved for focused transactional tasks (Refunds, Subscription Tier Switching, Stripe Connect, and Customer Portal subscription actions).
4. **Blade Component Decomposition**:
   - Decompose `app.blade.php` into clean partials under `resources/views/partials/admin/` (`styles`, `nav`, `modals`, `drawers`, `views/*`, `components/*`) while maintaining the single root view contract to keep all 128 PHPUnit tests passing green.
5. **Feature Exposure**:
   - Expose existing backend capabilities: 'Export CSV' for transaction ledgers and Email Notification template preview & test-send in Admin Settings.

## Consequences
- **Positive**: Exceptional merchant and customer experience matching luxury handmade pottery branding; elimination of layout shifts; clean maintainable Blade architecture; 100% test compatibility.
- **Trade-offs**: Requires disciplined client-side JavaScript modularization and synchronized DOM element ID preservation across partials.
