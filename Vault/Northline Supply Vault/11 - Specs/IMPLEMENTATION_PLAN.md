---
type: spec
doc: IMPLEMENTATION_PLAN
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[PRD]], [[TRD]], [[DATABASE_SCHEMA]], [[APP_FLOW]], [[DESIGN_BRIEF]]
---

# Sprint Plan: Northwind Ceramics Payment App (Sprint 0 to Sprint 5)

**Client:** Emma Carter, Northwind Ceramics (Melbourne, Australia)  
**Platform:** Shopify and Stripe  
**Document:** Sprint plan, version 1.0  
**Date:** October 11, 2026  
**Follows:** Requirements and design document (Part 1 and Part 2)

This plan breaks the 10-week build into six sprints. Each sprint has a goal, stories, tasks with day estimates, deliverables, a done check, tests, and risks. Story IDs match the design document.

---

## 1. Planning Basics

### Assumptions
- **Developer Capacity**: One developer works on this, counting 5 working days per week.
- **Estimates**: Estimates are in developer days and assume full-time work. If working part-time, scale every date by the same ratio and inform Emma immediately.
- **Sprint Duration**: Sprints are one to two weeks. Work that is not done moves to the next sprint; it is never hidden.
- **Buffer**: The plan has no spare days. Section 10 lists what to cut first if falling behind.

### Working Rhythm
- **Sprint Start**: 1 hour to confirm scope and tasks.
- **Weekly Check-in with Emma**: 30 minutes. Time zones differ, so send a short written update prior to the call.
- **Sprint End**: Demo on staging, followed by a short retro to document blockers.

### Definition of Done (DoD)
- Code is reviewed in a pull request, even when working as the sole developer.
- All tests pass in CI.
- The change is deployed to staging and verified manually.
- Webhook and payment code has an idempotency test.
- No secrets in the repo or Docker image.
- Docs and decision notes are updated in Obsidian.
- Demonstrated in the sprint demo.

### Sprint Overview
| Sprint | Weeks | Focus | Days | Stories |
|---|---|---|---|---|
| **0** | Week 1 | Setup and decisions | 5 | 0 |
| **1** | Weeks 2 to 3 | Install, Stripe, plans, and deposit checkout | 10 | 6 |
| **2** | Weeks 4 to 5 | Webhooks, orders, balance link, and emails | 10 | 5 |
| **3** | Week 6 | Dashboard, refunds, privacy, and MVP demo | 5 | 4 |
| **4** | Weeks 7 to 8 | Installments, retries, card update, and disputes | 10 | 4 |
| **5** | Weeks 9 to 10 | Subscriptions, portal, staff role, and launch | 10 | 5 |
| **Total** | **10 Weeks** | | **50** | **24** |

---

## 2. Story to Sprint Map

Every story from the design document sits in one sprint. Sprint 0 has no stories.

| Story | Title | Priority | Sprint | Linked Spec |
|---|---|---|---|---|
| **US-01** | Install through Shopify | Must | 1 | [[PRD#US-01]] |
| **US-02** | Connect Stripe | Must | 1 | [[PRD#US-02]] |
| **US-03** | Mark deposit-eligible products | Must | 1 | [[PRD#US-03]] |
| **US-04** | Set deposit amount | Must | 1 | [[PRD#US-04]] |
| **US-05** | Payment status on orders | Must | 2 | [[PRD#US-05]] |
| **US-06** | Balance link and reminders | Must | 2 | [[PRD#US-06]] |
| **US-07** | Merchant dashboard | Must | 3 | [[PRD#US-07]] |
| **US-08** | Refunds | Should | 3 | [[PRD#US-08]] |
| **US-09** | Edit email templates | Should | 2 | [[PRD#US-09]] |
| **US-10** | Installment plans | Should | 4 | [[PRD#US-10]] |
| **US-11** | Automatic retries | Should | 4 | [[PRD#US-11]] |
| **US-12** | Ceramic Club subscription | Should | 5 | [[PRD#US-12]] |
| **US-13** | Dispute alerts | Could | 4 | [[PRD#US-13]] |
| **US-14** | CSV export | Could | 5 | [[PRD#US-14]] |
| **US-15** | Staff login | Could | 5 | [[PRD#US-15]] |
| **US-16** | Deposit option on product page | Must | 1 | [[PRD#US-16]] |
| **US-17** | Pay deposit and get confirmation | Must | 1 | [[PRD#US-17]] |
| **US-18** | Pay balance by link | Must | 2 | [[PRD#US-18]] |
| **US-19** | Customer portal view | Should | 5 | [[PRD#US-19]] |
| **US-20** | Update card | Should | 4 | [[PRD#US-20]] |
| **US-21** | Pause, skip, cancel | Should | 5 | [[PRD#US-21]] |
| **US-22** | Webhooks processed once | Must | 2 | [[PRD#US-22]] |
| **US-23** | Privacy webhooks | Must | 3 | [[PRD#US-23]] |
| **US-24** | Audit log | Should | 3 | [[PRD#US-24]] |

---

## 3. Sprint Breakdown

### Sprint 0: Setup and decisions
- **Timing**: Week 1 (5 developer days)
- **Stories**: None (setup work)
- **Goal**: Get a working base so every later sprint starts fast. Settle the checkout option with the client.

| # | Task | Detail | Days |
|---|---|---|---|
| **S0-T1** | Decide checkout option (A or B) | Read Shopify docs on payments apps and order creation. Confirm the choice with Emma in writing. | 0.5 |
| **S0-T2** | Shopify and Stripe test accounts | Partner account, dev store, custom app, Stripe test keys, Stripe CLI for local webhooks. | 0.5 |
| **S0-T3** | Repository and Docker stack | Laravel app, PostgreSQL, Redis, env files, make targets. One command starts everything. | 1.0 |
| **S0-T4** | CI pipeline | GitHub Actions: lint, tests, build the image, push to GHCR tagged by commit SHA. | 1.0 |
| **S0-T5** | Staging deploy | VPS with Docker Compose, Caddy for HTTPS, deploy on merge to main. | 1.0 |
| **S0-T6** | Monitoring and logging | Sentry, structured logs, Uptime Kuma check on a health endpoint. | 0.5 |
| **S0-T7** | Backlog and rules | Project board, Definition of Done, short decision notes for the checkout choice. | 0.5 |
| **Total** | | | **5.0** |

- **Deliverables**: Running local stack and a staging URL with HTTPS; green CI pipeline; written checkout decision; Shopify dev store and Stripe test account ready.
- **Done When**: A new commit on main reaches staging without manual steps; health endpoint shows up in Uptime Kuma; Emma has confirmed checkout option.
- **Testing**: Smoke test (stack starts from clean clone); pipeline fails on a deliberately broken test.
- **Risks**: Shopify rules on payments are unclear. Fallback: Option A for MVP and note it.

---

### Sprint 1: Install, Stripe, plans, and deposit checkout
- **Timing**: Weeks 2 to 3 (10 developer days)
- **Stories**: US-01, US-02, US-03, US-04, US-16, US-17
- **Goal**: A test customer can choose a deposit on a product page and pay it. The merchant can set the plan.

| # | Task | Detail | Days |
|---|---|---|---|
| **S1-T1** | Core migrations | `shops`, `users`, `plans`, `plan_targets`, `customers`. | 1.0 |
| **S1-T2** | Shopify install flow | OAuth, encrypted token storage, `app/uninstalled` webhook. | 1.5 |
| **S1-T3** | Embedded admin shell | App Bridge, session tokens, Polaris layout, navigation. | 1.5 |
| **S1-T4** | Stripe connection | Connect the account, store keys safely, show status and a test call. | 1.0 |
| **S1-T5** | Plans API and screens | Create, edit, delete plans. Pick products or collections. Deposit as percent or fixed. | 2.0 |
| **S1-T6** | Deposit calculation service | Rounding rules in cents. Balance is always total minus deposit. | 0.5 |
| **S1-T7** | Storefront widget | Theme app extension that shows deposit and balance on the product page. | 1.5 |
| **S1-T8** | Start checkout | Create the Stripe Checkout session with card saving (Option B) or deposit variant (Option A). | 1.0 |
| **Total** | | | **10.0** |

- **Deliverables**: Installable app on dev store; plan setup screens; deposit option on product page; deposit payment completing in Stripe test mode.
- **Done When**: Installing and uninstalling leaves clean data; 30% deposit on $200.00 AUD shows $60.00 and $140.00; Stripe connection status is correct for valid and invalid keys.
- **Testing**: Unit tests for deposit math with odd cents and four currencies; feature tests for OAuth and plan CRUD; manual test with Stripe test cards.
- **Risks**: App Bridge and session tokens take longer than planned; theme app extension needs theme supporting app blocks.

---

### Sprint 2: Webhooks, orders, balance link, and emails
- **Timing**: Weeks 4 to 5 (10 developer days)
- **Stories**: US-22, US-05, US-06, US-18, US-09
- **Goal**: After a deposit, the order appears in Shopify with the right status. The customer gets reminders and can pay the balance.

| # | Task | Detail | Days |
|---|---|---|---|
| **S2-T1** | Order and payment migrations | `orders`, `payment_schedules`, `payments`, `webhook_events`, `email_templates`, `email_logs`. | 1.0 |
| **S2-T2** | Webhook receivers | Verify signatures, save raw event, unique key per event, answer 200 fast. | 1.5 |
| **S2-T3** | Event jobs | On checkout completed: create Shopify order and schedule rows for balance. | 2.0 |
| **S2-T4** | Status writeback | Payment status on Shopify order through tags or metafields. | 1.0 |
| **S2-T5** | Balance payment link | Signed, expiring link and payment page. Used or expired links show clear message. | 1.5 |
| **S2-T6** | Scheduler and reminders | Find due balances, send one reminder per step, log each email. | 1.5 |
| **S2-T7** | Email service and templates | Default templates, editor for subject and body, test send. | 1.5 |
| **Total** | | | **10.0** |

- **Deliverables**: Reliable webhook handling; order status visible in Shopify; working balance link; reminder emails with editor.
- **Done When**: Same Stripe event sent twice changes data once; deposit order shows "Deposit paid" with due date; reminder never sent twice for same step; expired link charges nothing.
- **Testing**: Repeat-delivery test for every webhook topic; saved sample events from Shopify and Stripe; scheduler test with fake clock; email test with preview and real inbox.
- **Risks**: Shopify order creation rules (Option B) block some order types; email lands in spam (setup SPF/DKIM early).

---

### Sprint 3: Dashboard, refunds, privacy, and MVP demo
- **Timing**: Week 6 (5 developer days)
- **Stories**: US-07, US-08, US-23, US-24
- **Goal**: The MVP is complete and ready to show Emma.

| # | Task | Detail | Days |
|---|---|---|---|
| **S3-T1** | Dashboard | Cash collected, balances due, upcoming charges, failed payments. | 1.5 |
| **S3-T2** | Order list filters | Filter and search by payment status. | 0.5 |
| **S3-T3** | Refund flow | Full and partial refund to Stripe, then to Shopify. Save both IDs. | 1.0 |
| **S3-T4** | Privacy webhooks | Customer data request, customer redact, shop redact. | 1.0 |
| **S3-T5** | Audit log | Refunds, plan changes, settings changes. | 0.5 |
| **S3-T6** | Demo prep | Demo data, demo script, bug fixes from the sprint. | 0.5 |
| **Total** | | | **5.0** |

- **Deliverables**: Merchant dashboard; refunds from app; privacy webhooks; MVP demo to client.
- **Done When**: Partial refund shows correctly in Stripe and Shopify; three privacy webhooks respond and delete right data; Emma runs full deposit-to-balance flow herself on staging.
- **Testing**: Refund tests for full, partial, and over-limit amounts; privacy webhook tests with sample payloads; full end-to-end run before demo.
- **Risks**: Demo feedback adds scope (log for Phase 2 unless it is a bug).

---

### Sprint 4: Installments, retries, card update, and disputes
- **Timing**: Weeks 7 to 8 (10 developer days)
- **Stories**: US-10, US-11, US-20, US-13
- **Goal**: A customer can split an order into payments that charge on their own. Failures are handled without manual work.

| # | Task | Detail | Days |
|---|---|---|---|
| **S4-T1** | Installment plan setup | Plan options such as 3 payments over 6 weeks. Schedule rows generated at checkout. | 2.0 |
| **S4-T2** | Plan selector in widget | Product page and cart show installment option. | 1.0 |
| **S4-T3** | Automatic charges | Charge saved payment method off-session. Handle 3D Secure and cards needing action. | 2.0 |
| **S4-T4** | Retry logic | Retry on days 1, 3, and 5. Flag order and alert merchant. No automatic cancel. | 1.5 |
| **S4-T5** | Card update page | `SetupIntent` page and emailed link so customers fix failed cards themselves. | 1.5 |
| **S4-T6** | Dispute alerts | Handle `charge.dispute.created` and notify merchant with order link. | 1.0 |
| **S4-T7** | End-to-end tests | 3-payment plan in Stripe test mode with success, decline, and 3D Secure cases. | 1.0 |
| **Total** | | | **10.0** |

- **Deliverables**: Installment plans; automatic retries; customer card update; dispute alerts.
- **Done When**: 3-payment plan runs from checkout to Paid without manual step; declined card moves order to `payment_failed` and retries on schedule; customer updates card and next retry succeeds.
- **Testing**: Fake clock tests for retry schedule; Stripe test cards for decline, insufficient funds, and 3D Secure; locking test (two workers cannot charge one schedule row).
- **Risks**: Off-session charges requiring 3DS fail (card update flow is fallback); time zones move due dates (store UTC and test around daylight saving).

---

### Sprint 5: Subscriptions, portal, staff role, and launch
- **Timing**: Weeks 9 to 10 (10 developer days)
- **Stories**: US-12, US-19, US-21, US-15, US-14
- **Goal**: The Ceramic Club works end to end. The app is checked, documented, and ready to hand over.

| # | Task | Detail | Days |
|---|---|---|---|
| **S5-T1** | Subscription model | Monthly, quarterly, annual plans through Stripe. Each renewal creates a Shopify order. | 2.5 |
| **S5-T2** | Pause, skip, cancel, proration | Customer actions and upgrade or downgrade math. | 1.5 |
| **S5-T3** | Customer portal | Magic link login, plans, upcoming charges, payment history, card update. | 2.0 |
| **S5-T4** | Staff role | Permissions: staff cannot refund or change Stripe settings. | 1.0 |
| **S5-T5** | CSV export | Transactions export with date and status filters. | 0.5 |
| **S5-T6** | Hardening | Dashboard load test with 10,000 orders, accessibility check, security review, backup restore test. | 1.5 |
| **S5-T7** | Docs and handover | Setup guide, API notes, merchant guide, final demo. | 1.0 |
| **Total** | | | **10.0** |

- **Deliverables**: Subscriptions and customer portal; staff role and CSV export; test results and security notes; documentation and final demo.
- **Done When**: Renewal creates Shopify order and payment record; dashboard loads under 2 seconds with 10,000 orders; restore from backup works on clean server; Emma accepts final demo.
- **Testing**: Renewal, pause, skip, and cancel tests; permission tests for staff role; load test on dashboard and webhook endpoints; WCAG 2.1 AA check on customer pages.
- **Risks**: Proration edge cases take longer than planned; sprint has the most scope (refer to cut list).

---

## 4. Go-Live Checklist

Executed at the end of Sprint 5 before the app touches real money:

1. **Stripe Live Credentials**: Switch Stripe from test mode to live keys. Store them as secrets, not in code.
2. **Webhook Registration**: Register live Stripe and Shopify webhook endpoints. Confirm signatures pass.
3. **App Installation**: Install the app on the live Northwind Ceramics store.
4. **Alerts Verification**: Verify operational alerts reach developer (failed payments, webhook failures, queue backlog, site down).
5. **Backup Restoration**: Run a restore from the latest backup on a clean server.
6. **Privacy Webhooks**: Confirm all three privacy webhooks respond properly.
7. **Release Image & Rollback**: Tag release image by commit SHA. Document rollback steps and test them once.
8. **Real-Money Verification**: Place one real low-value order, pay deposit and balance, then refund it.
9. **Email Inbox Audit**: Check transactional emails across Gmail, Outlook, and Apple Mail; confirm SPF and DKIM pass.
10. **Client Handover**: Hand merchant guide to Emma; obtain written sign-off.

---

## 5. Schedule Risk & Cut List

The plan uses all 50 days. Any slip pushes the end date. If behind at the end of Sprint 3 or Sprint 4, cut in this order (agree each cut with Emma first):

| Order | Cut Item | Story | What Emma Loses | Days Saved |
|:---:|---|---|---|:---:|
| **1** | CSV export | US-14 | Her accountant exports by hand from Stripe | 0.5 |
| **2** | Staff login | US-15 | One shared owner login for now | 1.0 |
| **3** | Dispute alerts | US-13 | She checks the Stripe dashboard for disputes | 1.0 |
| **4** | Email template editor | US-09 | Fixed templates that developer changes on request | 1.0 |
| **5** | Proration on plan changes | - | Upgrades and downgrades start next cycle | 1.0 |
| **Total Recoverable** | | | | **4.5** |

### Other Schedule Risks
- **Checkout Option Changes After Sprint 0**: Costs 2 to 4 days in Sprint 1 and Sprint 2. *Mitigation: Lock in Option B in writing in Sprint 0.*
- **Slow Client Response on Cancel Rules & Fees**: *Mitigation: Ask in Sprint 0, not later.*
- **Shopify or Stripe API Updates**: *Mitigation: Pin API versions.*
- **Unplanned Absence (Illness/Work)**: *Mitigation: Notify Emma early and adjust dates transparently.*

---

## Change Log
| Date | Change | Old → New | Reason |
|---|---|---|---|
| 2026-10-11 | Implementation plan baseline | Scaffold plan → Northwind Ceramics 6-Sprint plan v1.0 | Full implementation roadmap breakdown from client specification |
| 2026-10-11 | Official Sprint Plan v1.0 integration | High-level 36-task list → Exact 6-Sprint breakdown (S0 to S5, 50 dev days, 24 stories, Go-live checklist, 5-stage cut list) | Ingestion of client Sprint Plan document |
