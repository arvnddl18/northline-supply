# Current Project State: Northwind Ceramics Payment App

## Completed
- Complete 12-page requirements & design specification analyzed and digested.
- **Official 10-page Sprint Plan (Sprint 0 to Sprint 5, v1.0, 50 dev days, 24 stories) ingested and baseline established**:
  - `00 - Core Memory/CORE_MEMORY.md` updated with Northwind Ceramics context and milestone gates.
  - `01 - Project Memory/OVERVIEW.md` updated with client, rhythm, and phasing details.
  - `02 - Architecture/SYSTEM_ARCHITECTURE.md` updated with Laravel + React Polaris + PostgreSQL/Redis topology.
  - `03 - Decisions/ADR_002_CHECKOUT_STRATEGY.md` created (Option B primary, Option A fallback).
  - `04 - Requirements/FUNCTIONAL_REQUIREMENTS.md` updated (FR-1 through FR-10).
  - `05 - User Preferences/CONVENTIONS.md` updated with PCI SAQ-A, integer cents, Definition of Done, and idempotency conventions.
  - `11 - Specs/PRD.md` updated with all 10 FRs, 22 NFRs, and 24 User Stories.
  - `11 - Specs/TRD.md` updated with API contracts, security rules, and tech stack.
  - `11 - Specs/APP_FLOW.md` updated with 7-state payment lifecycle and user flows.
  - `11 - Specs/DESIGN_BRIEF.md` updated with Polaris standards and storefront constraints.
  - `11 - Specs/DATABASE_SCHEMA.md` updated with all 14 database models and ERD.
  - `11 - Specs/IMPLEMENTATION_PLAN.md` fully synchronized to official Sprint Plan v1.0 (Sprints 0 to 5, 50 developer days, deliverables, done-when criteria, testing, risks, 10-point Go-Live Checklist, 5-stage scope cut list).
  - `09 - Current State/DEVELOPMENT_KANBAN.md` populated with all 36 granular sprint tasks (`S0-T1` through `S5-T7`) tagged by sprint and linked to specs.
- **S0-T1 Completed**: Written decision record [[ADR_002_CHECKOUT_STRATEGY]] locked in: Option B (Stripe-First Checkout via App Proxy) confirmed as primary with Option A as emergency fallback. Defer tax/shipping to final balance, initialize Shopify order as `PARTIALLY_PAID`, hybrid balance trigger (estimated date + manual button), and non-refundable deposit terms once making begins.
- **S0-T2 Completed**: Shopify and Stripe test environment provisioned:
  - `shopify.app.toml` updated with required Option B scopes (`write_orders`, `write_customers`) and `[app_proxy]` route.
  - `.env.example` created with complete Shopify and Stripe AUD test configurations.
  - Stripe CLI v1.53.1 installed on host machine via winget.
  - `scripts/stripe-listen.ps1` and npm shortcuts (`npm run stripe:listen`, `npm run stripe:trigger`) established.
  - Complete runbook recorded in [[STRIPE_AND_SHOPIFY_SETUP]].

## In Progress
- **Sprint 0: Setup and decisions (Week 1, 5 developer days)**
  - `S0-T3`: Repository and Docker stack (PostgreSQL, Redis, env files, make targets).

## Blocked
- None.

## Next Logical Tasks
1. Complete `S0-T3`: Initialize Docker stack (PostgreSQL, Redis, service scaffolding, make targets).
2. Complete `S0-T4` & `S0-T5`: GitHub Actions CI pipeline and VPS staging deploy with Caddy HTTPS.

## Development Kanban
- Living board: [[DEVELOPMENT_KANBAN]] (synced from [[IMPLEMENTATION_PLAN]])
- Pinned visual guide with priority hierarchy (`#p0-must`, `#p1-should`, `#p2-could`), work type indicators (`#decision` vs `#dev`), sprint roadmap, and balanced 22px column breathing room.

## Last Updated
2026-10-11
