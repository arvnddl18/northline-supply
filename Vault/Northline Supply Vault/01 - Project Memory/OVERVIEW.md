# Project Overview: Northwind Ceramics Payment App

## Client & Background
- **Client**: Emma Carter, Owner of Northwind Ceramics (Melbourne, Australia).
- **Core Business**: Handmade, made-to-order ceramics sold worldwide (Australia, US, UK, EU) on Shopify.
- **Problem Statement**: Production lead times range from 3 to 6 weeks. Customers previously paid 100% upfront for orders they would not receive for over a month, hurting conversion rates and triggering refund disputes.
- **Solution**: A custom Shopify payment and billing app integrated with Stripe enabling:
  1. Deposits and pre-orders (percentage or fixed per item) with automated balance collection.
  2. Multi-part installment plans (e.g., 3 payments over 6 weeks or 4 monthly payments).
  3. "Ceramic Club" recurring monthly/quarterly/annual subscriptions.

## Stakeholder Roles
- **Merchant (Owner - Emma Carter)**: Full system access (Stripe setup, plans, refunds, financial analytics, CSV export).
- **Staff (Studio Assistant)**: Limited access (can manage orders and plan assignments; strictly forbidden from issuing refunds or changing Stripe settings).
- **Customer**: Buyers worldwide in Australia, US, UK, and EU paying in AUD, USD, GBP, or EUR.
- **System**: Background workers and cron scheduler executing charges, dunning retries, and webhook handling.

## Phasing Strategy
- **MVP (Weeks 1 to 6)**: Install & Stripe Connect, deposits, balance payment link & reminders, order payment status sync, webhooks, Polaris dashboard, refunds, Shopify privacy webhooks.
- **Phase 2 (Weeks 7 to 10)**: Installment plans, automated off-session retries, Ceramic Club subscriptions, customer self-service portal, staff role permissions, CSV export, dispute alerts.

## Architectural Foundation
- **Backend**: Laravel 11 / PHP 8.3 (REST API, Webhook handlers, Queue workers, Scheduler).
- **Admin UI**: React embedded in Shopify Admin using Shopify Polaris & App Bridge.
- **Storefront**: Shopify Theme App Extension PDP/cart widget (< 30 KB JS, < 100 ms).
- **Database & Cache**: PostgreSQL (amounts stored in whole cents) + Redis (queues & concurrency locks).
- **Payment Processing**: Stripe PaymentIntents, SetupIntents, Checkout, and Billing.
