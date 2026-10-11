---
type: spec
doc: IMPLEMENTATION_PLAN
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[PRD]], [[TRD]]
---

# Implementation Plan

## 1. Phases
| Phase | Goal | Deliverables | Exit Criteria | Status |
|-------|------|--------------|---------------|--------|
| Phase 1: Foundation | Scaffold app & setup memory brain | Template verification, living specs, vault setup | App builds cleanly, living specs verified | Active |
| Phase 2: Feature Development | Build Northline Supply custom modules | Custom routes, metaobjects, catalog sync | Merchant user stories accepted | Todo |
| Phase 3: Production Hardening | Deploy app & verify compliance | Production DB, Shopify app store review compliance | Production deployment successful | Todo |

## 2. Task Breakdown
| ID | Task | Phase | Depends On | Owner | Linked Spec | Status |
|----|------|-------|-----------|-------|-------------|--------|
| T-001 | Initialize Obsidian Memory Brain & Spec Layer | Phase 1 | - | Agent | [[PRD#F-001]] | Done |
| T-002 | Verify local dev server and Prisma migrations | Phase 1 | T-001 | Dev | [[TRD#1-Tech-Stack]] | Done |
| T-003 | Configure Playwright MCP & Agent Automation | Phase 1 | T-001 | Dev/Agent | [[TRD#1-Tech-Stack]] | Done |
| T-004 | Define Northline Supply core feature requirements | Phase 2 | T-001 | User/Dev | [[PRD#4-Features]] | In Progress |
| T-005 | Implement Product Catalog & Metaobject synchronization | Phase 2 | T-004 | Dev | [[PRD#F-002]] | Todo |
| T-006 | Build Merchant Supply Dashboard with Polaris & App Bridge | Phase 2 | T-005 | Dev | [[PRD#US-001]] | Todo |
| T-007 | Implement Webhook Ingestion & Error Boundaries | Phase 2 | T-005 | Dev | [[PRD#F-003]] | Todo |
| T-008 | Add Playwright E2E automation journeys for merchant flows | Phase 3 | T-006 | QA/Dev | [[TRD#1-Tech-Stack]] | Backlog |
| T-009 | Production database migration & App Store compliance | Phase 3 | T-007 | DevOps | [[TRD#3-Infrastructure]] | Backlog |

## 3. Milestones
- M1: Project and Memory Brain initialization complete (Current).
- M2: Core merchant supply workflow implemented.
- M3: Production readiness and compliance testing.

## 4. Build Order & Dependency Notes
- Memory & spec baseline established first.
- New features mapped into PRD before schema or API implementations.

## 5. Testing Plan (unit / integration / E2E / UAT)
- Linting: `npm run lint`
- Type checking: `npm run typecheck`
- Shopify App review checklist validation.

## 6. Deployment Plan (environments, release steps, rollback)
- Staging/Dev: `shopify app dev`
- Production: Containerized build with environment variables set via hosting provider.

## 7. Risks & Mitigations
- Concurrency on SQLite → Migrate to hosted DB (PostgreSQL/MySQL) when going live.

## 8. Definition of Done
- TypeScript and linter pass with 0 errors.
- Relevant spec documents updated with changes and logged in change log.

## Change Log
| Date | Change | Old → New | Reason |
|------|--------|-----------|--------|
| 2026-10-11 | Initial baseline plan | None → v1.0 | Project initialization from scaffold |
