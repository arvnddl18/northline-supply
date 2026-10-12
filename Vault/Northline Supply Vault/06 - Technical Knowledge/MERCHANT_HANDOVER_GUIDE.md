# Merchant Handover Guide: Northwind Ceramics Custom Payment App

**Target Audience**: Emma Carter (Studio Owner) & Northwind Ceramics Staff  
**App Version**: 1.0.0 (Production Ready)  
**Date**: October 12, 2026  
**Document Type**: Merchant Operations & Handover Manual (S5-T7)  

---

## 1. Overview & Daily Workflow

Welcome to the Northwind Ceramics custom payment system. This application is deeply integrated into your Shopify Admin and connects securely with your Stripe AUD merchant account to manage handmade pottery deposits, installment plans, and recurring Ceramic Club memberships.

### Key Daily Routine
1. **Check Dashboard Overview**: Log into Shopify Admin &rarr; Apps &rarr; **Northwind Ceramics Payments**. Review your 4 KPI counters:
   - **Total Cash Collected**: Total real revenue captured.
   - **Pending Balances Due**: Outstanding ceramic orders currently in production.
   - **Upcoming Charges**: Automated installment or subscription renewals scheduled this week.
   - **Failed Payments**: Any card declines requiring attention.
2. **Review Kiln-Ready Orders**: When a batch of bespoke tableware or ceramic vases is fired and ready for dispatch, navigate to **Orders** &rarr; select the order &rarr; click **Send Balance Link** (or allow the automated 7-day reminder scheduler to notify the customer).
3. **Monitor Ceramic Club Memberships**: View the **Subscriptions** tab to check active member counts, pause requests, or skipped renewal cycles.

---

## 2. Managing Pre-Order Deposit Plans

### Creating or Editing a Plan
- Navigate to **Payment Plans** &rarr; click **Create New Plan**.
- **Plan Types**:
  1. **Pre-order Deposit**:
     - *Percentage*: e.g., 30% or 50% paid upfront at checkout.
     - *Fixed Amount*: e.g., $100.00 AUD fixed deposit.
  2. **Installment Split**: 2 to 4 automated payments (e.g., 3 splits spaced bi-weekly). Odd remainder cents are automatically distributed across early payments with zero penny leakage.
  3. **Ceramic Club Subscription**: Recurring monthly or quarterly subscription for seasonal pottery kit deliveries.
- **Assigning Products**:
  - Assign to specific high-value stoneware products (e.g. 12-Piece Stoneware Dinner Set) or entire Shopify Collections.
  - If no products are specifically targeted, the plan can serve as the default shop-wide policy.

### Storefront Customer Experience
- When a customer visits a product page with an active deposit plan, the lightweight **Northwind Ceramics Storefront Widget** automatically renders below the price:
  - Displays: *"Pay $60.00 AUD deposit now &bull; Balance of $140.00 AUD due when kiln-ready"*.
  - When the customer clicks **Pre-Order Now**, they are routed directly to Stripe Checkout with their card securely saved for future balance settlement (Option B checkout flow).

---

## 3. Order Processing & Balance Settlements

### Payment Lifecycle States
Every ceramic order transitions through a clear, transparent financial state machine:
- **`deposit_paid`**: Customer has paid their initial deposit variant or Stripe session. Shopify order tag: `deposit-paid`. Balance schedule is generated.
- **`paid`**: Balance paid in full. Shopify order tag updated to `paid`. Fulfillment can safely proceed.
- **`installment_active`**: Split payment plan in progress with scheduled automated charges.
- **`failed`**: A scheduled balance or installment card charge failed after 3 retry attempts. Tagged `installment-failed`.
- **`refunded` / `partially_refunded`**: Deposit or balance returned via Stripe.
- **`disputed`**: Chargeback inquiry opened on Stripe. Tagged `dispute-opened`.

### Sending Balance Payment Links
- **Automated**: The system's background scheduler scans for orders reaching their estimated completion date and automatically emails customers a secure, 7-day cryptographically signed payment link.
- **Manual Overwrite**: You can generate or re-send a payment link at any time from the **Orders** tab &rarr; **Generate Balance Link**.

---

## 4. Failed Charges & Automated Smart Retries

If an off-session installment charge fails (e.g., expired card, insufficient funds):
1. **Automated Retry Engine (US-11)**:
   - **Attempt 1**: Retries automatically after 24 hours.
   - **Attempt 2**: Retries automatically after 48 hours.
   - **Attempt 3 (Terminal)**: If still failing, the order is flagged as `failed`, tagged with `installment-failed` in Shopify, and an email is dispatched to the customer containing a self-service card update link.
2. **Merchant Discretion**: To protect your relationship with loyal ceramic collectors, **the app never automatically cancels an order upon card failure**. You decide whether to hold the pottery, grant an extension, or release the piece.

---

## 5. Ceramic Club Subscriptions Management (Sprint 5)

### Merchant Controls (Subscriptions Tab)
In your embedded admin shell under **Subscriptions**:
- **Pause Membership**: Temporarily halt renewals for a traveling member (1 or 2 months, or custom date). Stripe pause collection is set to void.
- **Resume Membership**: Reactivates billing immediately; next renewal proceeds as normal.
- **Skip Cycle**: Advances the customer's `next_renewal_at` date by 1 full cycle without charging.
- **Change Plan Tier (Prorated)**: Upgrade a member from Standard to Deluxe. Stripe's native proration engine (`create_prorations`) calculates integer-cent credit/charges immediately, and an audit entry is created.
- **Cancel Membership**: Available strictly to the Store Owner (`role === 'owner'`).

### Customer Self-Service Portal (US-19 & US-21)
Customers can manage their own memberships and pottery pre-orders without calling the studio:
- Customers request a secure **Magic Link** via email (15-minute expiring signed URL).
- Inside their portal, customers can:
  - View all active pre-orders and click **Pay Balance**.
  - Update their saved credit card via Stripe Elements (`SetupIntent`).
  - Pause, Resume, Skip Next Month, or Switch Tier with real-time proration.
  - Cancel their subscription with an explicit confirmation step.

---

## 6. Staff Role Governance & Security Safeguards (US-15)

To protect studio finances when delegating tasks to studio assistants:
- **Primary Owner (Emma)**: Full administrative privileges (refunds, Stripe API credential management, subscription cancellations, CSV exports).
- **Staff Accounts (Assistant)**:
  - &check; Can view dashboard, orders, and payment plans.
  - &check; Can generate and re-send balance payment links.
  - &check; Can pause, resume, and skip subscription cycles.
  - &cross; **BLOCKED (HTTP 403)**: Cannot issue refunds to Stripe.
  - &cross; **BLOCKED (HTTP 403)**: Cannot disconnect or edit Stripe API keys.
  - &cross; **BLOCKED (HTTP 403)**: Cannot cancel recurring subscriptions.

---

## 7. Accounting & CSV Transaction Exports (US-14)

For your monthly bookkeeping and Australian Tax Office (ATO) compliance:
- Navigate to the **Orders** tab &rarr; click **Export Transactions (CSV)**.
- The stream generates a complete ledger including:
  - Payment Intent ID, Transaction Timestamp (UTC), Order Number, Shopify GID, Customer Email, Currency, Paid Amount (AUD), Refunded Amount, and Status.
- Memory usage is strictly bounded so exports will never crash or timeout regardless of order volume.

---

## 8. Troubleshooting & Emergency Support

| Symptom | Cause | Remedy |
|---|---|---|
| **Widget not showing on product** | Theme App Extension block not added to product template. | Go to Shopify &rarr; Online Store &rarr; Themes &rarr; Customize &rarr; Add Block &rarr; "Northwind Deposit Widget". |
| **Stripe Connection shows "Disconnected"** | API keys revoked or rotated in Stripe dashboard. | Go to Settings &rarr; Re-enter Restricted Secret Key and Publishable Key &rarr; Test Credentials. |
| **Customer says link expired** | Balance links and portal links expire after 7 days / 15 mins for security. | Click "Send Balance Link" on the order to issue a fresh signed link. |
| **Staff sees "Forbidden 403"** | Staff member tried to process a refund or cancel subscription. | Log in with Emma's Owner account to perform financial cancellations or refunds. |

---

*Handover manual prepared for Emma Carter &bull; Northwind Ceramics Melbourne.*
