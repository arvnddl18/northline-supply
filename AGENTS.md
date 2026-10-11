# Shopify App Development & Agent Operating Rules

This app is scaffolded from a Shopify app template. See the README for framework-specific details.

Use the [Shopify AI Toolkit](https://shopify.dev/docs/apps/build/ai-toolkit) for all Shopify API and platform work. If missing, install it in the agent host per that page (or `npx skills add Shopify/shopify-ai-toolkit --list` for skill-compatible hosts) — do not add tooling to this repo.

---

## Operating Rules & Development Guidelines

### 1. Living Specifications & Obsidian Memory
- The external Obsidian vault located at `Vault/Northline Supply Vault` is the authoritative persistent memory layer and living specification repository (`11 - Specs/`).
- Follow the `/obsidian-memory` protocol: check specs before coding, record decisions in `03 - Decisions/`, update `09 - Current State/CURRENT_STATE.md`, and sync `DEVELOPMENT_KANBAN.md` on task completion.
- Source code remains the authoritative record of actual reality; specs describe intent. Report drift and confirm with the user before altering baseline specs.

### 2. Engineering Standards (Ponytail Principles)
- The best code is the code never written. Solve the whole problem with the least new code.
- No speculative abstractions, wrappers, or "for later" boilerplate nobody asked for.
- Deletion beats addition. Keep structure and conventions already present in the codebase.
- Any pragmatic shortcut with a known limit must be tagged in code: `shortcut: <the limit>, <when to upgrade>`.
- Non-trivial logic must leave an automated test or assert-based self-check.

### 3. Backlog & Kanban Rules
- Follow `Vault/Northline Supply Vault/05 - User Preferences/BACKLOG_RULES.md`.
- **WIP Limit**: Strictly 1 task in `In Progress` at any time for the solo developer.
- Priority tag hierarchy: `#p0-must` > `#p1-should` > `#p2-could`.
- After completing any task, run `node scripts/sync-kanban.js` (or `npm run kanban:sync`) to synchronize the live board.

### 4. Definition of Done (DoD)
Every pull request and completed task must satisfy the 7-point DoD:
1. **Code Review**: Self-reviewed pull request; clean diff with zero bloat.
2. **Automated Tests**: Local and CI tests pass green (`php artisan test`, `npm run typecheck`, `npm run lint`).
3. **Staging Verification**: Deployed and manually verified on staging.
4. **Idempotency**: Webhook and payment code verified with idempotency keys and tests.
5. **Security & Privacy**: Zero raw card PAN/CVVs (PCI SAQ-A scope), encrypted tokens at rest, zero secrets in repo or images.
6. **Obsidian Memory**: Decisions and completed work documented; `CURRENT_STATE.md` updated.
7. **Demonstration Ready**: Ready to demo to Emma Carter at weekly check-in.
