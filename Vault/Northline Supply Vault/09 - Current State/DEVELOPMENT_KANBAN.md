---

kanban-plugin: board

---

## 📌 Legend & Key

- **Priority**<br>🔴 **#p0-must** Must Have<br>🟠 **#p1-should** Should Have<br>🔵 **#p2-could** Could Have
- **Work Type**<br>⚖️ **#decision** Decision<br>🛠️ **#dev** Development

## Backlog

- [ ] **S4-T4**: Retry logic #p1-should #dev #sprint4 #1.5d

## Todo



## In Progress



## Done

**Complete**
- [x] **S0-T2**: Shopify and Stripe test accounts (Dev store, Partner app, Stripe test keys AUD, Stripe CLI) #p0-must #dev #sprint0 #0.5d [[STRIPE_AND_SHOPIFY_SETUP]]
- [x] **S0-T1**: Decide checkout option (A or B) (Option B locked, Option A fallback) #p0-must #decision #sprint0 #0.5d [[ADR_002_CHECKOUT_STRATEGY]]
- [x] **INIT-01**: Ingest 12-page requirements & spec baseline (Agent) #p0-must #decision #sprint0 [[PRD]]
- [x] **INIT-02**: Establish 10-page Sprint Plan v1.0 (50 dev days, 24 stories) (Agent) #p0-must #decision #sprint0 [[IMPLEMENTATION_PLAN]]
- [x] **INIT-03**: Create Checkout Strategy Decision Record (Option B primary) (Agent) #p0-must #decision #sprint0 [[ADR_002_CHECKOUT_STRATEGY]]
- [x] **S0-T3**: Repository and Docker stack #p0-must #dev #sprint0 #1.0d
- [x] **S0-T4**: CI pipeline #p1-should #dev #sprint0 #1.0d
- [x] **S0-T5**: Staging deploy #p1-should #dev #sprint0 #1.0d
- [x] **S0-T6**: Monitoring and logging #p2-could #dev #sprint0 #0.5d
- [x] **S0-T7**: Backlog and rules #p1-should #decision #sprint0 #0.5d
- [x] **S1-T1**: Core migrations #p0-must #dev #sprint1 #1.0d
- [x] **S1-T2**: Shopify install flow #p0-must #dev #sprint1 #1.5d
- [x] **S1-T3**: Embedded admin shell #p0-must #dev #sprint1 #1.5d
- [x] **S1-T4**: Stripe connection #p0-must #dev #sprint1 #1.0d
- [x] **S1-T5**: Plans API and screens #p0-must #dev #sprint1 #2.0d
- [x] **S1-T6**: Deposit calculation service #p0-must #dev #sprint1 #0.5d
- [x] **S1-T7**: Storefront widget #p0-must #dev #sprint1 #1.5d
- [x] **S1-T8**: Start checkout #p0-must #dev #sprint1 #1.0d
- [x] **S2-T1**: Order and payment migrations #p0-must #dev #sprint2 #1.0d
- [x] **S2-T2**: Webhook receivers #p0-must #dev #sprint2 #1.5d
- [x] **S2-T3**: Event jobs #p0-must #dev #sprint2 #2.0d
- [x] **S2-T4**: Status writeback #p0-must #dev #sprint2 #1.0d
- [x] **S2-T5**: Balance payment link #p0-must #dev #sprint2 #1.5d
- [x] **S2-T6**: Scheduler and reminders #p1-should #dev #sprint2 #1.5d
- [x] **S2-T7**: Email service and templates #p1-should #dev #sprint2 #1.5d
- [x] **S3-T1**: Dashboard #p0-must #dev #sprint3 #1.5d
- [x] **S3-T2**: Order list filters #p0-must #dev #sprint3 #0.5d
- [x] **S3-T3**: Refund flow #p1-should #dev #sprint3 #1.0d
- [x] **S3-T4**: Privacy webhooks #p0-must #dev #sprint3 #1.0d
- [x] **S3-T5**: Audit log #p1-should #dev #sprint3 #0.5d
- [x] **S3-T6**: Demo prep #p1-should #dev #sprint3 #0.5d
- [x] **S4-T1**: Installment plan setup #p1-should #dev #sprint4 #2.0d
- [x] **S4-T2**: Plan selector in widget #p1-should #dev #sprint4 #1.0d
- [x] **S4-T3**: Automatic charges #p1-should #dev #sprint4 #2.0d
- [x] **S4-T5**: Card update page #p1-should #dev #sprint4 #1.5d
- [x] **S4-T6**: Dispute alerts #p1-should #dev #sprint4 #1.0d
- [x] **S4-T7**: End-to-end tests #p1-should #dev #sprint4 #1.0d
- [x] **S5-T1**: Subscription model #p1-should #dev #sprint5 #2.5d
- [x] **S5-T2**: Pause, skip, cancel, proration #p1-should #dev #sprint5 #1.5d
- [x] **S5-T3**: Customer portal #p1-should #dev #sprint5 #2.0d
- [x] **S5-T4**: Staff role #p1-should #dev #sprint5 #1.0d
- [x] **S5-T5**: CSV export #p1-should #dev #sprint5 #0.5d
- [x] **S5-T6**: Hardening #p0-must #dev #sprint5 #1.5d
- [x] **S5-T7**: Docs and handover #p0-must #dev #sprint5 #1.0d

%% kanban:settings
```
{"kanban-plugin":"board","tag-colors":[{"tagKey":"#p0-must","color":"#ffffff","backgroundColor":"#ef4444"},{"tagKey":"#p1-should","color":"#ffffff","backgroundColor":"#f97316"},{"tagKey":"#p2-could","color":"#ffffff","backgroundColor":"#3b82f6"},{"tagKey":"#decision","color":"#6b21a8","backgroundColor":"#f3e8ff"},{"tagKey":"#dev","color":"#0f766e","backgroundColor":"#f0fdfa"},{"tagKey":"#sprint0","color":"#047857","backgroundColor":"#ecfdf5"},{"tagKey":"#sprint1","color":"#0e7490","backgroundColor":"#ecfeff"},{"tagKey":"#sprint2","color":"#4338ca","backgroundColor":"#eef2ff"},{"tagKey":"#sprint3","color":"#6d28d9","backgroundColor":"#f5f3ff"},{"tagKey":"#sprint4","color":"#7e22ce","backgroundColor":"#faf5ff"},{"tagKey":"#sprint5","color":"#475569","backgroundColor":"#f8fafc"},{"tagKey":"#0.5d","color":"#64748b","backgroundColor":"#f1f5f9"},{"tagKey":"#1.0d","color":"#64748b","backgroundColor":"#f1f5f9"},{"tagKey":"#1.5d","color":"#64748b","backgroundColor":"#f1f5f9"},{"tagKey":"#2.0d","color":"#64748b","backgroundColor":"#f1f5f9"},{"tagKey":"#2.5d","color":"#64748b","backgroundColor":"#f1f5f9"}]}
```
%%
