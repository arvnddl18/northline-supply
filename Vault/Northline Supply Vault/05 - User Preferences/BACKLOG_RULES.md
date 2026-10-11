# Backlog Operating Rules: Northwind Ceramics Payment App

**Version:** 1.0  
**Effective Date:** October 12, 2026  
**Scope:** Developer task workflow, board hygiene, WIP management, and scope control.  
**Related Documents:** [[DEVELOPMENT_KANBAN]], [[IMPLEMENTATION_PLAN]], [[CURRENT_STATE]], [[CONVENTIONS]]

---

## 1. Core Principles

1. **Single Developer Reality**: One developer is building this 50-day project. Overhead must be near zero.
2. **Work is Never Hidden**: Work that is not done rolls forward to the next sprint and is immediately flagged to Emma Carter.
3. **Notion Kanban Board (via Notion MCP)**: The primary live task board lives in Notion, accessed directly through the Notion MCP server (see Section 37 of the memory protocol and [[ADR_004_NOTION_MCP_KANBAN]]). `DEVELOPMENT_KANBAN.md` is preserved as an optional markdown snapshot.

---

## 2. Work In Progress (WIP) Limits

- **WIP Limit = 1**: The developer may have **at most 1 task** in the `In Progress` column at any time (hard ceiling of 3 across the entire project).
- Context switching between incomplete tasks is prohibited. If an active task becomes blocked by external dependencies (e.g. Shopify Partner review or Stripe account approval):
  1. Move the card to `Blocked` in Notion via Notion MCP and fill in `Blocked Reason`.
  2. Document the blocker under `## Blocked` in [[CURRENT_STATE]].
  3. Pull the next highest-priority `#p0-must` task from `Ready`/`Todo`.

---

## 3. Board Column Definitions & Transitions

```text
[ Backlog ] ──► [ Todo ] ──► [ In Progress ] ──► [ Done ]
                      ▲               │
                      └─ Blocked/Undo ┘
```

| Column | Criteria for Entry | Criteria for Exit |
|---|---|---|
| **Backlog** | All planned tasks for future sprints (Sprints 2–5). | Moved to `Todo` during Sprint Planning when the upcoming sprint begins. |
| **Todo** | Tasks scheduled for the current sprint, ordered strictly by priority (`#p0-must` top). | Pulled into `In Progress` when the developer is ready to start (respecting WIP=1). |
| **In Progress** | The single task currently being implemented or researched. | All 7 DoD criteria satisfied (or marked blocked in `CURRENT_STATE`). |
| **Done** | Task implementation merged, verified on staging, and recorded in `08 - Completed Work/`. | Permanent archive. Completed tasks are never reopened; new bugs or enhancements get new task IDs. |

---

## 4. Tagging Taxonomy & Standardization

Every task on the board must have a complete set of standardized tags:

| Category | Allowed Tags | Description |
|---|---|---|
| **Priority** | `#p0-must`<br>`#p1-should`<br>`#p2-could` | Gating level: P0 blocks sprint completion; P1 is core product quality; P2 is optional refinement. |
| **Work Type** | `#dev`<br>`#decision` | Nature of task: `#dev` requires code/tests; `#decision` requires written consensus/ADR. |
| **Sprint** | `#sprint0` … `#sprint5` | Milestone sprint allocation matching [[IMPLEMENTATION_PLAN]]. |
| **Estimate** | `#0.5d`, `#1.0d`, `#1.5d`, `#2.0d`, `#2.5d` | Developer day estimate. 1 day = 8 productive engineering hours. |

---

## 5. Definition of Done (DoD) Gating Protocol

No task may be marked complete or moved into `## Done` without satisfying all 7 checks:

1. **Pull Request & Code Review**: Self-reviewed git diff; zero dead code, zero unrequested abstractions.
2. **Automated CI Tests**: Unit and feature test suite passes green in CI (`php artisan test`, `npm run typecheck`, `npm run lint`).
3. **Staging Verification**: Deployed to VPS staging environment and manually verified.
4. **Idempotency Gating**: Every payment trigger or webhook receiver must prove idempotency with an automated test.
5. **Zero Secrets / PCI SAQ-A**: No credentials in code/logs/Docker images; zero raw credit card handling.
6. **Obsidian Memory & Notion Board Synchronization**:
   - Create completion record in `08 - Completed Work/<TASK_ID>_<NAME>.md`.
   - Update `09 - Current State/CURRENT_STATE.md`.
   - Update the task card on the Notion Kanban board to `Done` (setting `Completed` date) via Notion MCP.
7. **Client Demo Ready**: Feature demonstrated or prepared for weekly demo with Emma.

---

## 6. Scope Cut Protocol (Handling Schedule Delays)

If unexpected delays or blockers threaten the 10-week schedule, scope is reduced in strict 5-stage order as agreed in Section 10 of [[IMPLEMENTATION_PLAN]]. Never compromise testing or security.

1. **Cut Stage 1 — Subscriptions & Recurring Club (Sprint 5)**:  
   *Saves up to 10 days.* Defer Ceramic Club recurring subscriptions and customer portal; launch with deposits and installments only.
2. **Cut Stage 2 — Self-Service Customer Portal (Sprint 5)**:  
   *Saves up to 4 days.* Customers update cards and view payment status via customer support links instead of a dedicated portal.
3. **Cut Stage 3 — Installment Plans & Automatic Retries (Sprint 4)**:  
   *Saves up to 10 days.* Launch with core pre-order deposits + manual balance links only (US-01 through US-09).
4. **Cut Stage 4 — Merchant Analytics Dashboard & Polish (Sprint 3)**:  
   *Saves up to 3 days.* Use raw Shopify order tags and Stripe dashboard for reporting; defer custom analytics charts.
5. **Absolute MVP Floor (Non-Negotiable)**:  
   - US-01 (Install)
   - US-02 (Connect Stripe)
   - US-03 / US-04 (Deposit Plans)
   - US-16 / US-17 (Storefront Deposit Checkout)
   - US-05 / US-18 (Shopify Order Creation & Balance Collection)
   - US-22 / US-23 (Webhook Reliability & GDPR Privacy)

---

## 7. Synchronization Tooling & Notion MCP

- **Notion Kanban Integration**:
  The developer/agent interacts with the Notion Kanban board directly using Notion MCP tools (`Section 37`).
- **Markdown Snapshot Backup**:
  `node scripts/sync-kanban.js` (or `npm run kanban:sync`) can still be run to generate a static Markdown snapshot in [[DEVELOPMENT_KANBAN]].
- **Offline Sync Queue**:
  If the Notion MCP is unavailable, pending changes are buffered in `10 - Temporary/NOTION_SYNC_QUEUE.md` and replayed once the connection is restored.

