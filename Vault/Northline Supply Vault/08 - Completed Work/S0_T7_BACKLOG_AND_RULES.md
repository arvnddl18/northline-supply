# Completed Work: S0-T7 Backlog and Rules

## Overview
- **Task ID**: `S0-T7`
- **Title**: Backlog and rules
- **Priority**: `#p1-should`
- **Work Type**: `#decision`
- **Sprint**: Sprint 0
- **Estimate**: 0.5d
- **Status**: Completed
- **Date**: October 12, 2026

## Objectives Achieved
1. **Repository Pull Request Template (`.github/pull_request_template.md`)**:
   - Enforced the 7-point Definition of Done (DoD) agreed with Emma Carter across all pull requests.
   - Structured sections for Task ID, Sprint, Obsidian reference, git diff summary, test verification, and DoD gating checklist.
2. **Repository Developer & Agent Operating Rules (`AGENTS.md`)**:
   - Upgraded `AGENTS.md` to define operating principles across the Shopify AI Toolkit, living specifications in Obsidian (`11 - Specs/`), Ponytail code minimization standards, single-developer WIP limits, and DoD gating.
3. **Client-Facing Checkout Decision Brief (`03 - Decisions/CHECKOUT_CHOICE_EMMA_NOTE.md`)**:
   - Produced a clean, non-technical 1-page briefing for client Emma Carter explaining why Option B (Stripe-First via App Proxy) was chosen over Option A.
   - Clarified how secure off-session card saving works, why pottery taxes and shipping are deferred until pieces are completed, clear non-refundable deposit terms for custom craft production, and emergency fallback provisions.
   - Cross-linked to technical record [[ADR_002_CHECKOUT_STRATEGY]] and living specs.
4. **Dedicated Backlog Operating Rules (`05 - User Preferences/BACKLOG_RULES.md`)**:
   - Formalized WIP limits (strict limit of 1 active task in `In Progress` for solo developer).
   - Standardized column transition criteria (Backlog ➔ Todo ➔ In Progress ➔ Done).
   - Documented tag taxonomy (`#p0-must`, `#p1-should`, `#p2-could`, `#dev`, `#decision`, `#sprintX`, `#Xd`).
   - Codified the non-negotiable 5-stage scope reduction sequence (from Section 10 of Implementation Plan) to protect sprint deadlines without cutting tests or security.
5. **Project Conventions Update (`05 - User Preferences/CONVENTIONS.md`)**:
   - Cross-linked `BACKLOG_RULES.md` and repository PR template into project conventions.
6. **Board Synchronization (`09 - Current State/DEVELOPMENT_KANBAN.md`)**:
   - Synchronized living board state using `scripts/sync-kanban.js`.

## Verification
- Verified `.github/pull_request_template.md` renders markdown and checklist syntax cleanly.
- Verified `AGENTS.md` maintains Shopify toolkit rules and extends developer guidelines without conflict.
- Verified `BACKLOG_RULES.md` links correctly to `DEVELOPMENT_KANBAN.md`, `IMPLEMENTATION_PLAN.md`, and `CURRENT_STATE.md`.
- Ran `node scripts/sync-kanban.js` and confirmed board cleanly moves `S0-T7` to completed state.

## Related Files
- `.github/pull_request_template.md`
- `AGENTS.md`
- `Vault/Northline Supply Vault/03 - Decisions/CHECKOUT_CHOICE_EMMA_NOTE.md`
- `Vault/Northline Supply Vault/05 - User Preferences/BACKLOG_RULES.md`
- `Vault/Northline Supply Vault/05 - User Preferences/CONVENTIONS.md`
- `Vault/Northline Supply Vault/09 - Current State/CURRENT_STATE.md`
- `Vault/Northline Supply Vault/09 - Current State/DEVELOPMENT_KANBAN.md`
- `Vault/Northline Supply Vault/11 - Specs/IMPLEMENTATION_PLAN.md` (S0-T7)
