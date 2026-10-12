---
type: decision
id: ADR_008
title: Complete Frontend Redesign Architecture & Six-Surface Design System Implementation
status: Accepted
date: 2026-10-12
deciders:
  - Emma Carter (Merchant Owner)
  - Solo Lead Front-End Developer
consulted:
  - Antigravity AI Agent
  - code-explorer subagent
  - a11y-architect subagent
  - code-reviewer subagent
related:
  - "[[ADR_007_ARTISAN_UI_UX_POLARIS_AND_EMIL_MODERNIZATION]]"
  - "[[FRONTEND_REDESIGN_MASTER_SPEC]]"
  - "[[CONVENTIONS]]"
  - "[[DESIGN_BRIEF]]"
---

# ADR 008: Complete Frontend Redesign Architecture & Six-Surface Design System Implementation

## Context
Following initial MVP completion and modularization, a comprehensive design architecture overhaul was initiated to elevate Northline Supply (Northwind Ceramics) to an artisanal, luxury pottery studio experience matching the interactive prototype in `reference/`. 

The system encompasses six distinct operational surfaces:
1. **Surface 1: Merchant Embedded Admin Shell** (Shopify App Bridge v4, Overview, Plans, Orders Ledger, Subscriptions, Studio Audit, Settings).
2. **Surface 2: Storefront Theme App Extension & Simulated PDP** (Deposit calculation widget, installment selectors, checkout interception).
3. **Surface 3: Customer Self-Service Portal** (Magic link authentication, Ceramic Club delivery controls, order milestones).
4. **Surface 4: Hosted Pre-Order Balance Settlement** (5-step Kiln Stepper, invoice breakdown, paid and expired state variations).
5. **Surface 5: Hosted Card Update** (Installment context, Stripe SetupIntent Elements tokenization, retry schedule notes).
6. **Surface 6: Interactive Developer & Testing Hub** (/demo cockpit, live health strip, 4-flow cards, PDP simulator, terminal commands).

## Decisions

### 1. Unified Pottery Design System & Shared Stylesheet
- Extracted and centralized `public/css/northwind.css` from the baseline prototype, establishing a consistent aesthetic across embedded admin and hosted customer touchpoints.
- Enforced the Northwind Ceramics earthy artisan palette:
  - `--nc-clay-50` (`#FAF7F5`), `--nc-clay-100` (`#F3ECE7`), `--nc-clay-200` (`#E6D8CE`).
  - `--nc-terracotta` (`#C46238`, `#AB502B`), wash (`#FAF2ED`).
  - `--nc-sage` (`#31563A`), wash (`#EBF2EC`) — WCAG 2.2 contrast 6.16:1.
  - `--nc-ochre` (`#784C07`), wash (`#FBF4E8`) — WCAG 2.2 contrast 6.94:1.
  - `--nc-charcoal-900` (`#1C1917`), `--nc-charcoal-600` (`#57534E`).
- Paired `Playfair Display` serif typography with `Inter` sans-serif body copy and `DM Mono` for ledger/audit codes.

### 2. Full Architectural Redesign Across All Six Surfaces
- **Surface 1 (Admin Shell)**: Transformed the navigation into a 242px pottery studio sidebar with kiln firing progress ("Autumn firing · #08 · 72%"), maker profile for Emma Carter, 4 metric KPI cards (`#kpi-collected`, `#kpi-balance-due`, `#kpi-upcoming`, `#kpi-failed`), CSS cash flow bar chart, and slide-over drawers with Emil Kowalski spring kinematics (`--ease-drawer: cubic-bezier(.32, .72, 0, 1)`).
- **Surface 2 (Storefront)**: Upgraded theme app extension CSS tokens to terracotta and created dedicated `/storefront` route with interactive PDP, choice cards, and checkout interception dialog.
- **Surface 3 (Customer Portal)**: Rebuilt with serene 2-column layout, customer initials avatar, mini kiln stage steppers on order cards, and accessible lifecycle modals for pause, skip, tier switch, and cancellation.
- **Surface 4 (Balance Settlement)**: Implemented the 5-step Kiln Stepper card (30% Deposit -> Wheel thrown -> Glazed -> Final balance -> Courier dispatch) and detailed invoice breakdown.
- **Surface 5 (Card Update)**: Integrated installment context card, shimmer loading skeleton, and Stripe SetupIntent card tokenization.
- **Surface 6 (Developer Hub)**: Redesigned `/demo` with kinetic orbit kiln graphic, live health status pills, 4-flow navigation cards, and 1-click clipboard terminal commands.

### 3. Strict Backend Compatibility & DOM ID Preservation
- Preserved 100% of existing JavaScript DOM IDs, form action routes, signed URLs, and App Bridge token acquisition methods (`window.shopify.idToken()`).
- Synchronized topbar breadcrumb text and failed-order navigation counters with live ledger state.

### 4. WCAG 2.2 AA Accessibility & Emil Kowalski Micro-Interactions
- Tactile button press physics: `:active { transform: scale(0.965); }`.
- Visible `:focus-visible` ring with 2px offset for keyboard navigation.
- Accessible focus trapping with `aria-modal="true"`, `role="dialog"`, ESC key dismissal, and `inert` background locking.
- 44x44px minimum touch targets across all mobile and desktop controls.

### 5. Ponytail Engineering Discipline
- Zero bloat or unnecessary framework dependencies. Pure CSS, modern HTML5, and vanilla JavaScript.
- Full verification passed green: `npm run typecheck` (exit 0) and `npm run lint` (exit 0).

## Consequences
- **Positive**: Complete visual and architectural alignment with luxury Australian pottery brand identity; unified design tokens; zero functional regressions; pristine test and lint compliance.
- **Maintenance**: Design tokens are shared via `public/css/northwind.css`, allowing future styling adjustments to cascade cleanly across all surfaces.
