<!-- ============================================== -->
<!-- NORTHWIND CERAMICS SLIDE-OVER DRAWERS (SHEETS) -->
<!-- ============================================== -->

<!-- 1. Plan Builder Slide-Over Drawer -->
<aside id="nc-plan-drawer" class="nc-drawer-container" style="display: none;" role="presentation" aria-hidden="true">
    <div class="nc-drawer-scrim" onclick="window.closePlanModal()"></div>
    <div class="nc-drawer-sheet" role="dialog" aria-modal="true" aria-labelledby="plan-drawer-title">
        <header class="nc-drawer-header">
            <div class="nc-drawer-title-group">
                <p class="eyebrow" style="margin-bottom: 4px;">STUDIO PLAN BUILDER</p>
                <h3 id="plan-drawer-title">Create Deposit Plan</h3>
            </div>
            <button type="button" class="nc-btn-close" onclick="window.closePlanModal()" aria-label="Close drawer">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </header>

        <div class="nc-drawer-body">
            <form id="plan-form" onsubmit="event.preventDefault(); window.savePlan();" class="form-stack">
                <input type="hidden" id="plan-edit-id" value="">

                <label class="field-label" for="plan-name-input">
                    <span>Plan Title <span class="required-mark">*</span></span>
                    <input type="text" id="plan-name-input" class="nc-input" placeholder="e.g. 30% Made-to-Order Ceramic Deposit" required>
                    <small class="field-hint">Customer-facing title shown during PDP checkout and order receipts.</small>
                </label>

                <label class="field-label" for="plan-type-select">
                    <span>Payment Model <span class="required-mark">*</span></span>
                    <select id="plan-type-select" class="nc-select" onchange="window.togglePlanTypeFields(); window.updatePlanPreview();">
                        <option value="deposit">Deposit Pre-order Plan (Upfront + Balance Before Shipping)</option>
                        <option value="installment">Installment Split Plan (2–4 Equal Off-Session Payments)</option>
                    </select>
                </label>

                <!-- Deposit Settings Section -->
                <div id="plan-deposit-settings" class="field-row">
                    <label class="field-label" for="plan-deposit-type">
                        <span>Calculation Method</span>
                        <select id="plan-deposit-type" class="nc-select" onchange="window.updatePlanPreview()">
                            <option value="percentage">Percentage (e.g. 30%)</option>
                            <option value="fixed">Fixed Cents (e.g. 5000 = $50.00 AUD)</option>
                        </select>
                    </label>
                    <label class="field-label" for="plan-deposit-value">
                        <span>Deposit Value <span class="required-mark">*</span></span>
                        <input type="number" id="plan-deposit-value" class="nc-input" value="30" min="1" required oninput="window.updatePlanPreview()">
                        <small id="plan-deposit-value-hint" class="field-hint">Enter percentage (1–99) or fixed cents.</small>
                    </label>
                </div>

                <!-- Installment Settings Section -->
                <div id="plan-installment-settings" class="field-row" style="display: none;">
                    <label class="field-label" for="plan-installments-count">
                        <span>Split Payments Count</span>
                        <select id="plan-installments-count" class="nc-select" onchange="window.updatePlanPreview()">
                            <option value="2">2 Split Payments</option>
                            <option value="3" selected>3 Split Payments</option>
                            <option value="4">4 Split Payments</option>
                        </select>
                    </label>
                    <label class="field-label" for="plan-installment-frequency">
                        <span>Billing Cadence</span>
                        <select id="plan-installment-frequency" class="nc-select" onchange="window.updatePlanPreview()">
                            <option value="1_week">Weekly (Every 1 week)</option>
                            <option value="2_week" selected>Bi-Weekly (Every 2 weeks)</option>
                            <option value="1_month">Monthly (Every 1 month)</option>
                        </select>
                    </label>
                </div>

                <!-- Live Calculation Preview Card -->
                <div class="calc-preview">
                    <div class="calc-preview-top">
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/>
                            </svg>
                            LIVE CALCULATION PREVIEW
                        </span>
                        <span style="color: var(--nc-charcoal-600);">(Sample $200.00 AUD Stoneware Vase)</span>
                    </div>

                    <div id="preview-deposit-container" style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 10px;">
                        <div>
                            <span style="color: var(--nc-charcoal-600); font-size: 11px; text-transform: uppercase; font-weight: 600;">Due Today (Deposit)</span>
                            <div id="preview-deposit-label" style="font-family: var(--serif); font-size: 19px; font-weight: 700; color: var(--terracotta); margin-top: 2px;">$60.00 AUD</div>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: var(--nc-charcoal-600); font-size: 11px; text-transform: uppercase; font-weight: 600;">Due When Shipped</span>
                            <div id="preview-balance-label" style="font-family: var(--serif); font-size: 19px; font-weight: 700; color: var(--ink); margin-top: 2px;">$140.00 AUD</div>
                        </div>
                    </div>

                    <div id="preview-installment-container" style="display: none; flex-direction: column; gap: 6px; font-size: 12px; margin-top: 10px;">
                        <div id="preview-installment-list"></div>
                    </div>
                </div>

                <label class="field-label" for="plan-product-targets">
                    <span>Specific Product IDs <span class="field-optional">(OPTIONAL)</span></span>
                    <input type="text" id="plan-product-targets" class="nc-input" placeholder="gid://shopify/Product/1001, gid://shopify/Product/1002" style="font-family: var(--mono); font-size: 11px;">
                    <small class="field-hint">Target individual items. Leave blank for shop-wide default plan.</small>
                </label>

                <label class="field-label" for="plan-collection-targets">
                    <span>Specific Collection IDs <span class="field-optional">(OPTIONAL)</span></span>
                    <input type="text" id="plan-collection-targets" class="nc-input" placeholder="gid://shopify/Collection/5001" style="font-family: var(--mono); font-size: 11px;">
                </label>

                <div style="display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; background: var(--clay-50); border: 1px solid var(--line); border-radius: 8px;">
                    <input type="checkbox" id="plan-active-input" checked style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--terracotta); cursor: pointer;">
                    <div>
                        <label for="plan-active-input" style="font-size: 13px; font-weight: 600; cursor: pointer; color: var(--ink);">Active on Storefront</label>
                        <p style="font-size: 11px; color: var(--nc-charcoal-600); margin: 2px 0 0;">Eligible for App Proxy deposit checkout immediately upon creation.</p>
                    </div>
                </div>

                <div class="drawer-footer">
                    <button type="button" class="button button-secondary" onclick="window.closePlanModal()">Cancel</button>
                    <button type="submit" class="button button-primary" id="btn-save-plan">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</aside>

<!-- 2. Order Schedule Inspector Slide-Over Drawer -->
<aside id="nc-order-drawer" class="nc-drawer-container" style="display: none;" role="presentation" aria-hidden="true">
    <div class="nc-drawer-scrim" onclick="window.closeOrderDrawer()"></div>
    <div class="nc-drawer-sheet" role="dialog" aria-modal="true" aria-labelledby="order-drawer-title">
        <header class="nc-drawer-header">
            <div class="nc-drawer-title-group">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span id="order-drawer-status-badge" class="status-pill status-ochre">Deposit Paid</span>
                    <span id="order-drawer-order-number" style="font-size: 12px; color: var(--nc-charcoal-600); font-family: var(--mono);">#---</span>
                </div>
                <h3 id="order-drawer-title">Pre-Order Payment Schedule</h3>
            </div>
            <button type="button" class="nc-btn-close" onclick="window.closeOrderDrawer()" aria-label="Close drawer">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </header>

        <div class="nc-drawer-body" id="order-drawer-body">
            <!-- Loading Skeleton -->
            <div id="order-drawer-loading" style="text-align: center; padding: 40px 10px;">
                <div class="skeleton-bar" style="width: 100%; height: 80px; border-radius: 8px; margin-bottom: 16px;"></div>
                <div class="skeleton-bar" style="width: 100%; height: 160px; border-radius: 8px;"></div>
            </div>

            <div id="order-drawer-content" style="display: none;">
                <!-- Financial Overview Card -->
                <div class="drawer-financials">
                    <div>
                        <span>TOTAL ORDER</span>
                        <strong id="drawer-total-amount">$0.00</strong>
                    </div>
                    <div>
                        <span>PAID DEPOSIT</span>
                        <strong id="drawer-paid-amount" style="color: var(--sage);">$0.00</strong>
                    </div>
                    <div>
                        <span>BALANCE DUE</span>
                        <strong id="drawer-balance-due" style="color: var(--ochre);">$0.00</strong>
                    </div>
                </div>

                <!-- Customer Details -->
                <section class="drawer-section">
                    <div class="drawer-section-title">
                        <h3>Customer details</h3>
                    </div>
                    <div style="font-size: 12px; color: var(--ink); line-height: 1.8;">
                        <div><strong style="color: var(--nc-charcoal-600);">Email:</strong> <span id="drawer-customer-email">---</span></div>
                        <div><strong style="color: var(--nc-charcoal-600);">Stripe Customer:</strong> <code id="drawer-stripe-customer-id" style="font-family: var(--mono); font-size: 11px;">---</code></div>
                    </div>
                </section>

                <!-- Payment Timeline & Schedules -->
                <section class="drawer-section">
                    <div class="drawer-section-title">
                        <h3>Payment progression</h3>
                        <span class="soft-dot" style="font-size: 10px; color: var(--nc-charcoal-600);">Automated billing</span>
                    </div>
                    <div id="drawer-schedules-container" class="nc-timeline">
                        <!-- Rendered dynamically -->
                    </div>
                </section>

                <!-- Transaction History -->
                <section class="drawer-section">
                    <div class="drawer-section-title">
                        <h3>Stripe payments</h3>
                    </div>
                    <div id="drawer-payments-list" style="font-size: 12px;">
                        <!-- Payments rendered dynamically -->
                    </div>
                </section>

                <!-- Email Logs -->
                <section class="drawer-section">
                    <div class="drawer-section-title">
                        <h3>Customer notifications</h3>
                    </div>
                    <div id="drawer-emails-list" style="font-size: 11px; color: var(--nc-charcoal-600);">
                        <!-- Emails rendered dynamically -->
                    </div>
                </section>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="button button-secondary" id="drawer-btn-copy-link" onclick="window.drawerCopyPaymentLink()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="8" y="8" width="11" height="12" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h2"/>
                </svg>
                Copy Payment Link
            </button>
            <button type="button" class="button button-danger" id="drawer-btn-refund" onclick="window.drawerOpenRefund()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 3.5h11.5L21 7v13.5l-3-1.8-3 1.8-3-1.8-3 1.8-3-1.8z"/><path d="M9 10h6M9 14h7"/>
                </svg>
                Refund Order
            </button>
        </div>
        <div class="prototype-note" style="margin: 0; padding: 10px 25px 14px; background: #fffdfa; border-top: 1px dashed var(--line); font-size: 11px; color: var(--nc-charcoal-600); display: flex; align-items: center; gap: 6px;">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
            </svg>
            Schedule preview · dual-system reconciliation verified
        </div>
    </div>
</aside>
