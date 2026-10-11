# Client Decision Brief: How Customers Pay for Deposits & Balances

**To:** Emma Carter, Northwind Ceramics  
**From:** Lead Software Engineer  
**Date:** October 12, 2026  
**Subject:** Checkout Strategy Decision (Option B: Direct Stripe Pre-Order Flow)  
**Related Technical Record:** [[ADR_002_CHECKOUT_STRATEGY]] | **Specs:** [[PRD]], [[APP_FLOW]]

---

## 1. Executive Summary

To make made-to-order ceramic purchases feel effortless for your customers while saving you hours of manual invoice chasing, we have selected **Option B (Direct Stripe Pre-Order Flow via App Proxy)** as our primary checkout approach.

This approach gives your customers a seamless checkout experience and allows your custom payment app to securely save their payment card so that remaining balances and future installment payments can be processed automatically when their ceramics are finished.

---

## 2. Why Option B Over Option A?

| Consideration | Option A (Shopify Variant Hack) | Option B (Direct Stripe Flow - Selected) |
|---|---|---|
| **Deposit Payment** | Standard Shopify Checkout | Seamless Stripe Checkout via Storefront |
| **Card Saving** | ❌ Cannot save cards for future charges | ✅ Securely saves card with customer consent |
| **Balance Collection** | ⚠️ You must email a link; customer must re-enter card | ✅ Automated charge when pieces are ready (or customer link) |
| **Installments & Ceramic Club** | ❌ Impossible to automate | ✅ Fully supports installments & monthly subscriptions |
| **Customer Friction** | High (customer re-enters details every payment) | Minimal (one-click pre-order approval) |

---

## 3. How the Customer Experience Works

1. **Deposit Upfront**:  
   On your product page, the customer sees clear pricing: for example, **"Pay $60.00 AUD deposit now, balance of $140.00 AUD due upon completion."**
2. **Secure One-Step Payment**:  
   When they click **Pre-Order with Deposit**, Stripe opens securely. Stripe takes the 30% deposit and safely vault-saves their card token for future balance settlement.
3. **Shopify Order Created Instantly**:  
   As soon as Stripe confirms the deposit, an order automatically appears in your Shopify Admin marked as **Partially Paid** with tags `#deposit-paid` and `#balance-due`.
4. **Deferred Shipping & Taxes**:  
   Because pottery weight and packaging vary, exact shipping costs and local taxes are calculated and added to the final balance invoice rather than guessed upfront.
5. **Effortless Final Settlement**:  
   When you finish throwing, glazing, and firing the order (or on your estimated delivery date), the remaining balance can be collected automatically against their saved card, or triggered via a quick **"Request Balance Now"** button in your Shopify dashboard.

---

## 4. Clear Policies & Terms

- **Non-Refundable Custom Production**:  
  To protect your studio from sudden cancellations after clay is shaped and materials are committed, checkout clearly informs buyers that deposits become non-refundable once studio production begins.
- **Merchant Refund Discretion**:  
  You retain 100% control inside your embedded Shopify dashboard to issue full or partial refunds directly back to the customer whenever circumstances warrant.

---

## 5. Risk Safeguard (Emergency Fallback)

If Shopify API sandbox or review timelines ever delay order creation during Sprint 1 testing, we have architected **Option A (Deposit Variant)** as a pre-designed, ready-to-activate emergency fallback. You will never be stuck without a working store.

---

*Please confirm you are happy with this strategy or let us know if you'd like any adjustments to the customer messaging before we implement the storefront widgets in Sprint 1.*
