# Problems, Risks & Open Questions: Northwind Ceramics Payment App

## Open Questions Under Client Review
1. **Cancellation Deposit Policy**: What happens to a deposit when a customer cancels?
   - *Status*: Awaiting written policy from Emma Carter before production commences.
   - *Recommendation*: Non-refundable deposit retained if cancelled after production has begun to cover bespoke clay and glaze materials.
2. **Stripe Processing Fees**: Who pays Stripe transaction fees?
   - *Status*: Needs client confirmation during Sprint 0.
   - *Options*: Absorb into retail pricing (recommended for conversion) or add a surcharge if allowed by jurisdiction.
3. **Afterpay in Australia**: Do we need to support Afterpay alongside deposits?
   - *Status*: Afterpay is common with Australian buyers but conflicts with split deposit schedules. Recommend offering Afterpay only for full payments or non-deposit items.

## Architectural Risks & Mitigations
1. **Checkout Option B vs Shopify Policies**:
   - *Risk*: Using Stripe Checkout via App Proxy redirects outside native checkout.
   - *Mitigation*: Option B is custom-app specific for Emma's store. Option A (Deposit Variant) is prepared as a modular fallback.
2. **Multi-Currency Cents Integrity**:
   - *Risk*: Discrepancies between Shopify order totals and Stripe intents across AUD, USD, GBP, and EUR.
   - *Mitigation*: Store all monetary values as integer cents (`BIGINT`); enforce `balance = total - deposit` rounding formula.
3. **Chargebacks & Disputes on Bespoke Items**:
   - *Risk*: Chargebacks on deposits during the 3–6 week production period.
   - *Mitigation*: Automated webhook alert on `charge.dispute.created`, direct dashboard deep link, and automatic capture of terms acceptance evidence.
