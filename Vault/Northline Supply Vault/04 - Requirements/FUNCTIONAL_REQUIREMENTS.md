# Functional Requirements: Northwind Ceramics Payment App

## FR-1: Merchant Onboarding and Stripe Connection
- **1.1**: Install through the Shopify OAuth flow.
- **1.2**: Connect merchant's own Stripe account (Stripe Connect or API keys) and show connection status in real-time.
- **1.3**: Show a setup checklist: Connect Stripe → Create first payment plan → Turn on storefront widget.

## FR-2: Deposit and Pre-order Payments
- **2.1**: Mark specific products or collections as deposit eligible.
- **2.2**: Set the deposit as a percentage or a fixed amount per product.
- **2.3**: Charge the deposit at checkout. Schedule the balance automatically (when item ships or on a fixed date).
- **2.4**: Send a secure payment link for the balance, with automatic reminders.

## FR-3: Installment Plans
- **3.1**: Create plans such as 3 payments over 6 weeks or 4 monthly payments.
- **3.2**: Show the plan option on the product page and in the cart through a theme app extension.
- **3.3**: Charge saved payment methods automatically on schedule.
- **3.4**: Retry failed payments on days 1, 3, and 5. Then flag the order for merchant attention (no automatic cancel).

## FR-4: Subscriptions (Ceramic Club)
- **4.1**: Offer monthly, quarterly, and annual subscription products.
- **4.2**: Let customers pause, skip, cancel, or change their payment method in a self-service portal.
- **4.3**: Create a Shopify order for each successful renewal so Emma can fulfill it.
- **4.4**: Prorate when a customer upgrades or downgrades.

## FR-5: Order and Payment Synchronization
- **5.1**: Handle Shopify webhooks (`orders/create`, `orders/cancelled`, `refunds/create`) and Stripe webhooks (`payment_intent.succeeded`, `payment_intent.payment_failed`, `invoice.*`, `charge.dispute.created`).
- **5.2**: Show payment status on the Shopify order (`paid`, `partially paid`, `balance due`, `failed`) using tags and metafields.
- **5.3**: Stop or warn merchant before fulfilling an order with an unpaid balance.

## FR-6: Refunds, Cancellations, and Disputes
- **6.1**: Issue full or partial refunds from the app. Updates must reflect synchronously in both Stripe and Shopify.
- **6.2**: Apply automatic cancellation rules (e.g. deposit kept if customer cancels after production starts).
- **6.3**: Notify merchant of Stripe disputes with a direct link to the order.

## FR-7: Customer Notifications
- **7.1**: Send branded emails for deposit received, upcoming charge, failed payment, balance due, and subscription renewal.
- **7.2**: Let merchant edit email templates and configure the reminder schedule.

## FR-8: Customer Portal
- **8.1**: Passwordless login (magic link or Shopify customer account SSO).
- **8.2**: Let customers view plans, upcoming charges, and payment history, and update card details through Stripe-hosted elements.

## FR-9: Merchant Dashboard and Reporting
- **9.1**: Overview of cash collected, outstanding balances, upcoming charges, failed payments, and active subscribers.
- **9.2**: Filter and search orders by payment status.
- **9.3**: Export transactions to CSV.

## FR-10: Settings and Roles
- **10.1**: Two roles: `Owner` (Emma - full access) and `Staff` (assistant - no refunds, no Stripe settings).
- **10.2**: Support AUD, USD, GBP, and EUR, following Shopify order currency.
- **10.3**: Leave tax and shipping to Shopify. The app never recalculates them.
