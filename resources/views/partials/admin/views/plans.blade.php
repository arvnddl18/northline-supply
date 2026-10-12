<!-- TAB: PAYMENT PLANS -->
<div id="tab-plans" class="tab-content" role="tabpanel" aria-labelledby="tab-btn-plans">
    <div class="section-heading">
        <div>
            <p class="eyebrow">PAYMENT BLUEPRINTS</p>
            <h1>Plans made for the making.</h1>
            <p class="section-copy">Shape deposits and split payments around the time it takes to make something well.</p>
        </div>
        <button class="button button-primary" type="button" onclick="window.openPlanModal()">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span>Create new plan</span>
        </button>
    </div>

    <!-- Toolbar Row -->
    <div class="toolbar-row">
        <div class="toolbar-note">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 6.5h14M5 12h14M5 17.5h9"/><circle cx="17.5" cy="17.5" r="2.5"/>
            </svg>
            <span>
                <strong id="plans-count-label">Studio payment plans</strong>
                <small>Available at checkout whenever a piece is made to order</small>
            </span>
        </div>
    </div>

    <!-- Plan Alert Banner -->
    <div id="plan-alert" style="display: none; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 13px;"></div>

    <!-- Plans Table Panel -->
    <div class="panel table-panel plan-table-panel" id="plans-table-container">
        <div class="table-title-row">
            <div>
                <h2>Your payment plans</h2>
                <span>Applies to made-to-order catalog items</span>
            </div>
            <div class="filter-pills">
                <button class="filter-pill filter-pill-active" type="button">All plans</button>
            </div>
        </div>

        <!-- Empty State -->
        <div id="plans-empty-state" class="empty-state" style="display: none; padding: 60px 24px; text-align: center;">
            <span class="empty-icon" style="display: inline-grid; place-items: center; width: 54px; height: 54px; margin-bottom: 16px; border-radius: 50%; color: var(--terracotta); background: #fbf2ed;">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: 28px; height: 28px;">
                    <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/><path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                </svg>
            </span>
            <h3 style="font: 600 20px/1.2 var(--serif); margin-bottom: 8px;">No payment plans configured yet</h3>
            <p style="color: var(--ink-soft); font-size: 13px; max-width: 440px; margin: 0 auto 20px;">
                Create a deposit or installment plan to give collectors flexible terms while securing studio production costs.
            </p>
            <button class="button button-primary" type="button" onclick="window.openPlanModal()">Create First Plan</button>
        </div>

        <!-- Plans Data Table -->
        <div class="table-scroll">
            <table id="plans-table" class="data-table">
                <thead>
                    <tr>
                        <th>PLAN NAME</th>
                        <th>TYPE</th>
                        <th>DEPOSIT / TERMS</th>
                        <th>APPLIES TO</th>
                        <th>STATUS</th>
                        <th style="text-align: right;"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="plans-tbody">
                    @include('partials.admin.components.table-skeleton', ['rows' => 3, 'cols' => 6])
                </tbody>
            </table>
        </div>

        <div class="table-foot" style="display: flex; justify-content: space-between; padding: 14px 20px; border-top: 1px solid var(--line); color: var(--ink-soft); font-size: 11px;">
            <span>Plan changes apply to new checkouts only</span>
            <span class="foot-note" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 14px; height: 14px;"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/></svg>
                DATABASE CONNECTED
            </span>
        </div>
    </div>

    <div class="page-tip" style="display: flex; align-items: center; gap: 12px; margin-top: 24px; padding: 16px 20px; border-radius: 10px; background: #fffdfa; border: 1px solid var(--line); font-size: 12px; color: var(--ink-soft);">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--terracotta)" stroke-width="1.6" style="width: 20px; height: 20px; flex-shrink: 0;"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/></svg>
        <p style="margin: 0; flex: 1;">
            <strong>Thoughtful payment terms help people say yes.</strong> A deposit protects the time, clay, and kiln firings that go into every handmade piece.
        </p>
        <a href="/storefront" target="_blank" class="text-link">See storefront preview →</a>
    </div>
</div>
