---
type: spec
doc: PRD
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[TRD]], [[APP_FLOW]], [[IMPLEMENTATION_PLAN]]
---

# Product Requirements Document

## 1. Overview
- Product name: Northline Supply
- One-line summary: Embedded Shopify application enabling Northline Supply merchant workflows and catalog operations.
- Problem being solved: Managing merchant supply workflows, product synchronization, and metaobject definitions within Shopify Admin.
- Vision / success definition: Seamless embedded merchant experience with robust Shopify API integration and secure session management.

## 2. Target Users
| Persona | Description | Goals | Pain Points |
|---------|-------------|-------|-------------|
| Merchant Admin | Store owner or catalog manager using Shopify Admin | Manage supply, products, and app configuration smoothly | Context switching between multiple external portals |

## 3. Goals & Success Metrics
- Embedded Admin Integration → Zero friction within Shopify Admin iframe (100% App Bridge compliance).
- Reliable Authentication → Seamless OAuth and offline session storage via Prisma SQLite.

## 4. Features
| ID | Feature | Description | Priority (Must/Should/Could) | Status |
|----|---------|-------------|------------------------------|--------|
| F-001 | Embedded Shell | Shopify App Bridge embedded dashboard | Must | Active |
| F-002 | Product & Metaobject Scopes | Access to write products and manage metaobject definitions | Must | Active |
| F-003 | Webhook Ingestion | Ingest app/uninstalled and app/scopes_update events | Must | Active |

## 5. User Stories & Acceptance Criteria
- US-001: As a merchant, I want to access Northline Supply within Shopify Admin so that I don't leave my store dashboard.
  - Acceptance: Given a merchant session, When visiting `/app`, Then the embedded app UI loads inside Shopify Admin via App Bridge.

## 6. Scope
### In Scope
- Shopify Embedded Admin App via React Router & App Bridge.
- SQLite Prisma Session storage.
- Standard Shopify compliance webhooks (`app/uninstalled`, `app/scopes_update`).
### Out of Scope (explicit)
- Public non-embedded consumer storefront components.
### Future Considerations
- Custom supply management and inventory allocation modules.

## 7. Assumptions, Risks, Dependencies
- Dependency: Shopify CLI and Shopify Partner App credentials.
- Risk: SQLite session concurrency in high-scale production (migrate to PostgreSQL/MySQL when deploying beyond dev).

## 8. Open Questions
- Specific supply and catalog workflows tailored to Northline Supply business logic.

## Change Log
| Date | Change | Old → New | Reason |
|------|--------|-----------|--------|
| 2026-10-11 | Initial baseline spec | None → v1.0 | Project initialization from scaffold |
