---
type: spec
doc: PRD
status: Active
version: 1.0
last_updated: 2026-10-11
confidence: VERIFIED
related: [[TRD]], [[APP_FLOW]], [[DESIGN_BRIEF]], [[DATABASE_SCHEMA]], [[IMPLEMENTATION_PLAN]]
---

# Product Requirements Document: Northwind Ceramics Payment App

## 1. Overview
- **Product Name**: Northwind Ceramics Payment App
- **Client**: Emma Carter, Northwind Ceramics (Melbourne, Australia)
- **One-line Summary**: Custom Shopify payment application integrating Stripe to collect pre-order deposits, automated scheduled balance payments, installment plans, and recurring Ceramic Club subscriptions.
- **Problem Being Solved**: Handmade, made-to-order ceramic production takes 3 to 6 weeks. Customers paying 100% upfront for orders delivered over a month later leads to checkout drop-offs, lowered conversions, and refund disputes.
- **Vision & Success Definition**: Seamless payment flexibility for buyers worldwide (Australia, US, UK, EU) that protects merchant cash flow, automates balance charging without manual chasing, and powers recurring subscription revenue.

## 2. Target Users & Roles
| Role | Persona | Permissions & Scope | Primary Goals |
|---|---|---|---|
| **Merchant Owner** | Emma Carter | Full access across all settings, Stripe connection, plans, and financial actions | Manage payment plans, oversee cash flow, issue refunds, export accounting reports |
| **Merchant Staff** | Part-time studio assistant | Operational access; strictly restricted from issuing refunds or modifying Stripe credentials | Check order payment statuses, verify balance due dates, assist with production planning |
| **Customer** | Buyer in AU, US, UK, EU | Storefront & self-service portal | Pay deposit upfront, pay balances via secure links, manage cards & subscriptions |
| **System** | Background jobs & scheduler | Automated runtime | Process webhooks, charge scheduled balances, run retry engines, dispatch emails |

## 3. Goals & Success Metrics
- **Conversion Uplift**: Eliminate upfront price friction by offering low upfront deposits (e.g. 30% or $50 AUD).
- **Automated Balance Recovery**: 100% automated scheduled balance link reminders and off-session card charges, reducing manual merchant follow-ups to zero.
- **Zero Double-Billing**: 100% idempotent webhook ingestion and payment intent triggers.
- **Delivery Timeline**: Working demo in 8 to 10 weeks across two phases (MVP in Weeks 1–6, Phase 2 in Weeks 7–10).

## 4. Functional Requirements (FR-1 to FR-10)

### FR-1: Merchant Onboarding and Stripe Connection
- **1.1**: Install through the Shopify OAuth flow.
- **1.2**: Connect merchant's own Stripe account (Stripe Connect or API keys) and show connection status in real-time.
- **1.3**: Show a setup checklist: Connect Stripe → Create the first payment plan → Turn on the storefront widget.

### FR-2: Deposit and Pre-order Payments
- **2.1**: Mark specific products or collections as deposit eligible.
- **2.2**: Set the deposit as a percentage or a fixed amount per product.
- **2.3**: Charge the deposit at checkout. Schedule the balance automatically (when the item ships or on a fixed date).
- **2.4**: Send a secure payment link for the balance, with automatic reminders.

### FR-3: Installment Plans
- **3.1**: Create plans such as 3 payments over 6 weeks or 4 monthly payments.
- **3.2**: Show the plan option on the product page and in the cart through a theme app extension.
- **3.3**: Charge saved payment methods automatically on schedule.
- **3.4**: Retry failed payments (e.g. on days 1, 3, and 5). Then flag the order for merchant attention (no automatic cancel).

### FR-4: Subscriptions (Ceramic Club)
- **4.1**: Offer monthly, quarterly, and annual subscription products.
- **4.2**: Let customers pause, skip, cancel, or change their payment method in a self-service portal.
- **4.3**: Create a Shopify order for each successful renewal, so Emma can fulfill it.
- **4.4**: Prorate when a customer upgrades or downgrades.

### FR-5: Order and Payment Synchronization
- **5.1**: Handle Shopify webhooks (`orders/create`, `orders/cancelled`, `refunds/create`) and Stripe webhooks (`payment_intent.succeeded`, `payment_intent.payment_failed`, `invoice.*`, `charge.dispute.created`).
- **5.2**: Show payment status on the Shopify order (`paid`, `partially paid`, `balance due`, `failed`) with tags or metafields.
- **5.3**: Stop or warn merchant before fulfilling an order with an unpaid balance.

### FR-6: Refunds, Cancellations, and Disputes
- **6.1**: Issue full or partial refunds from the app. They must show in both Stripe and Shopify.
- **6.2**: Apply automatic cancellation rules (e.g. deposit kept if customer cancels after production starts).
- **6.3**: Notify merchant of Stripe disputes, with a link to the order.

### FR-7: Customer Notifications
- **7.1**: Send branded emails for deposit received, upcoming charge, failed payment, balance due, and subscription renewal.
- **7.2**: Let merchant edit email templates and configure the reminder schedule.

### FR-8: Customer Portal
- **8.1**: Passwordless login (magic link or Shopify customer account SSO).
- **8.2**: Let customers view plans, upcoming charges, and payment history, and update card details through Stripe-hosted elements.

### FR-9: Merchant Dashboard and Reporting
- **9.1**: Overview of cash collected, outstanding balances, upcoming charges, failed payments, and active subscribers.
- **9.2**: Filter and search orders by payment status.
- **9.3**: Export transactions to CSV.

### FR-10: Settings and Roles
- **10.1**: Two roles: `Owner` (Emma - full access) and `Staff` (assistant - no refunds, no Stripe settings).
- **10.2**: Support AUD, USD, GBP, and EUR, following the Shopify order currency.
- **10.3**: Leave tax and shipping to Shopify. The app never recalculates them.

## 5. Non-Functional Requirements (NFR-1 to NFR-22)

### Security
- **NFR-1**: Never store raw card data. Use Stripe Elements or Checkout to stay at PCI SAQ-A scope.
- **NFR-2**: Verify HMAC signature on every Shopify webhook and signing secret on every Stripe webhook.
- **NFR-3**: Encrypt access tokens and API keys at rest (AES-256). Use HTTPS everywhere. Keep secrets in environment variables or a secrets manager.
- **NFR-4**: Role-based access control. Audit logs for refunds, plan changes, and settings changes.

### Reliability and Data Integrity
- **NFR-5**: Payment operations are idempotent (Stripe idempotency keys). No customer is ever charged twice.
- **NFR-6**: Webhook processing is idempotent and safe to retry, with a dead-letter queue for failures.
- **NFR-7**: 99.5% monthly uptime. Scheduled charges run through a job queue that survives restarts.
- **NFR-8**: Daily database backups with a tested restore procedure.

### Performance and Scalability
- **NFR-9**: The storefront widget adds under 100 ms to page load and under 30 KB of JavaScript.
- **NFR-10**: Dashboard pages load in under 2 seconds with up to 10,000 orders.
- **NFR-11**: Webhook endpoints answer in under 5 seconds (Shopify's limit; acknowledge in < 1 second, process later in queue).
- **NFR-12**: Multi-tenant ready (all tables keyed by `shop_id`), in case the app is offered to other merchants.

### Compliance and Privacy
- **NFR-13**: GDPR and CCPA compliance, including Shopify's mandatory privacy webhooks (`customers/data_request`, `customers/redact`, `shop/redact`).
- **NFR-14**: Collect only needed data. Set retention limits after account closure.
- **NFR-15**: Meet Shopify's app requirements (App Bridge, session tokens, Billing API for app charges).

### Usability and Accessibility
- **NFR-16**: Embedded admin built with Shopify Polaris, so it matches the Shopify admin.
- **NFR-17**: Works on mobile, enabling order management from mobile devices.
- **NFR-18**: Customer-facing screens meet WCAG 2.1 AA.
- **NFR-19**: English at launch. Code is structured so translation can be added later.

### Maintainability and Operations
- **NFR-20**: Docker containers, CI/CD pipeline with automated tests, and separate staging and production environments.
- **NFR-21**: Structured logging, error tracking (Sentry), and alerts for failed payments, webhook failures, and queue backlogs (Uptime Kuma).
- **NFR-22**: Automated tests for payment flows using Stripe test mode. Documentation for the code and API.

## 6. User Stories & Acceptance Criteria

| ID | Role | User Story | Priority | Phase |
|---|---|---|---|---|
| **US-01** | Merchant | I want to install the app through Shopify, so I can start without a developer. | Must | MVP |
| **US-02** | Merchant | I want to connect my Stripe account, so payments go to my own account. | Must | MVP |
| **US-03** | Merchant | I want to mark products or collections as deposit eligible, so only made-to-order items ask for a deposit. | Must | MVP |
| **US-04** | Merchant | I want to set the deposit as a percentage or a fixed amount, so I can match my costs. | Must | MVP |
| **US-05** | Merchant | I want to see the payment status on every order, so I know what is paid and what is owed. | Must | MVP |
| **US-06** | Merchant | I want the app to send a balance payment link and reminders, so I stop chasing customers by hand. | Must | MVP |
| **US-07** | Merchant | I want a dashboard of cash collected and balances due, so I can plan my month. | Must | MVP |
| **US-08** | Merchant | I want to refund a payment in full or in part from the app, so Shopify and Stripe stay in sync. | Should | MVP |
| **US-09** | Merchant | I want to edit the email templates, so messages sound like my brand. | Should | MVP |
| **US-10** | Merchant | I want to build installment plans, so customers can split big orders. | Should | Phase 2 |
| **US-11** | Merchant | I want failed payments retried automatically, so I recover money without effort. | Should | Phase 2 |
| **US-12** | Merchant | I want to sell a Ceramic Club subscription, so I get steady monthly income. | Should | Phase 2 |
| **US-13** | Merchant | I want an alert when a customer opens a dispute, so I can respond in time. | Could | Phase 2 |
| **US-14** | Merchant | I want to export transactions to CSV, so my accountant can use them. | Could | Phase 2 |
| **US-15** | Owner | I want to give my assistant a limited staff login, so she cannot refund or change Stripe settings. | Could | Phase 2 |
| **US-16** | Customer | I want to see the deposit option on the product page, so I know I do not have to pay everything now. | Must | MVP |
| **US-17** | Customer | I want to pay the deposit and get a confirmation email, so I know my order is safe. | Must | MVP |
| **US-18** | Customer | I want to pay the balance through a secure link, so I can finish when I am ready. | Must | MVP |
| **US-19** | Customer | I want to log in and see my plan and upcoming charges, so there are no surprises. | Should | Phase 2 |
| **US-20** | Customer | I want to update my card myself, so a failed payment does not cancel my order. | Should | Phase 2 |
| **US-21** | Customer | I want to pause, skip, or cancel my subscription, so I stay in control. | Should | Phase 2 |
| **US-22** | System | I want to verify and process every Shopify and Stripe webhook only once, so no payment is counted twice. | Must | MVP |
| **US-23** | System | I want to handle Shopify's privacy webhooks, so the app meets GDPR and CCPA. | Must | MVP |
| **US-24** | System | I want to log refunds, plan changes, and settings changes, so there is an audit trail. | Should | MVP |

### Detailed Acceptance Criteria for Main MVP Stories
- **US-04**: Given a product with a 30 percent deposit and a price of 200.00 AUD, when a customer chooses the deposit option, then the deposit is 60.00 AUD and the balance is 140.00 AUD. Amounts round to the cent, and the balance is always total minus deposit.
- **US-05**: Given a paid deposit, when I open the order, then I see the status 'Deposit paid - balance due' and the due date.
- **US-06**: Given a balance due in 7 days, when the reminder job runs, then the customer gets one email with a working payment link. No second email goes out for the same reminder step.
- **US-17**: Given a successful deposit payment, when Stripe confirms it, then the customer receives the confirmation email within 2 minutes.
- **US-18**: Given a valid link, when the customer pays, then the order moves to 'Paid' and the merchant is notified. An expired or used link shows a clear message and no charge happens.
- **US-22**: Given the same Stripe event is delivered twice, when both reach the server, then the data changes once and both requests return 200.

## 7. Scope & Phasing
### MVP (Weeks 1 to 6)
- Shopify OAuth install flow & Stripe account connection.
- Product/collection deposit plan configuration (% or fixed).
- Storefront deposit widget & Stripe-first checkout flow (Option B).
- Shopify order creation, tag writeback, and schedule row generation.
- Balance payment link generation & 7-day reminder emails.
- Merchant Polaris dashboard (cash collected, balances due).
- Full & partial refund issuance from embedded app.
- Webhook signature validation, idempotency, and Shopify mandatory privacy webhooks.
### Phase 2 (Weeks 7 to 10)
- Multi-installment plans (e.g., 3 bi-weekly payments / 6 weeks or 4 monthly).
- Automated off-session charge scheduler and retries (Days 1, 3, 5).
- Ceramic Club subscriptions (monthly, quarterly, annual renewals with automated Shopify fulfillment order creation).
- Customer self-service portal (magic link login, plan overview, card updates via Stripe Elements).
- Staff role permissions (restricted from refunds and Stripe credentials).
- CSV transaction export for bookkeeping.
- Stripe dispute alerting and links to evidence.
### Explicitly Out of Scope
- Custom recalculation of taxes and shipping (handled strictly by Shopify).
- Foreign currency exchange rate manipulation (follows Shopify order currency).

## 8. Constraints, Risks & Open Questions
### Platform & Budget Constraints
- Platform: Shopify (Admin GraphQL API, theme app extensions, webhooks) and Stripe.
- Budget: Modest MVP; tight 10-week timeline requires holding Phase 2 items until MVP is accepted.
### Client Questions & Risks
| Item | Risk & Why It Matters | Mitigation / Action |
|---|---|---|
| **Checkout Option (A or B)** | Dictates checkout integration architecture. | Option B is selected for the custom app; Option A kept as fallback. Verified with client in Sprint 0. |
| **Deposit on Cancellation** | Client policy on non-refundable deposits affects dispute liabilities. | Obtain explicit written cancellation policy from Emma before Sprint 1. |
| **Stripe Fee Allocation** | Determines whether Stripe fees are absorbed or passed on. | Clarify with Emma during Sprint 0. |
| **Afterpay Demand** | Common with Australian shoppers; could conflict with custom deposit flow. | Clarify if Afterpay is required for non-deposit products. |
| **Multi-currency Rounding** | Discrepancies between currencies. | Store all values in cents, test AUD, USD, GBP, EUR explicitly. |
| **Disputes & Chargebacks** | Risk of customer charging back deposit. | Instant merchant alert on dispute webhook; link order and preserve evidence. |

## Change Log
| Date | Change | Old → New | Reason |
|---|---|---|---|
| 2026-10-11 | Full specification baseline | Generic template → Northwind Ceramics v1.0 | Complete requirements ingestion from client specification document |
