<!-- TAB: SUBSCRIPTIONS -->
<div id="tab-subscriptions" class="tab-content" role="tabpanel" aria-labelledby="tab-btn-subscriptions">
    <div class="section-heading">
        <div>
            <p class="eyebrow">CERAMIC CLUB</p>
            <h1>A little clay, on a rhythm.</h1>
            <p class="section-copy">Keep member deliveries in step with the seasons and the studio’s capacity.</p>
        </div>
        <button class="button button-secondary" type="button" onclick="window.loadSubscriptions()">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 7v5h-5M5 17v-5h5"/><path d="M6.5 9a6.5 6.5 0 0 1 11-2L19 8M5 16l1.5 1a6.5 6.5 0 0 0 11-2"/>
            </svg>
            <span>Refresh Roster</span>
        </button>
    </div>

    <!-- Subscriptions KPI Grid -->
    <div class="sub-kpi-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px;">
        <article class="sub-kpi" style="padding: 16px 20px; border: 1px solid var(--line); border-radius: 10px; background: #ffffff; position: relative;">
            <span style="display: block; font-size: 9px; font-weight: 700; color: var(--ink-soft); letter-spacing: 1.2px; text-transform: uppercase;">ACTIVE MEMBERS</span>
            <strong id="sub-kpi-active" style="display: block; margin: 8px 0 4px; font: 500 28px/1 var(--serif); color: var(--ink);">0</strong>
            <small style="color: var(--sage); font-size: 10px; font-weight: 600;">Active recurring subscribers</small>
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="position: absolute; top: 16px; right: 16px; width: 20px; height: 20px; color: var(--sage);"><circle cx="9" cy="8" r="3"/><path d="M3.5 19c.4-3 2.3-4.5 5.5-4.5s5.1 1.5 5.5 4.5"/></svg>
        </article>

        <article class="sub-kpi" style="padding: 16px 20px; border: 1px solid var(--line); border-radius: 10px; background: #ffffff; position: relative;">
            <span style="display: block; font-size: 9px; font-weight: 700; color: var(--ink-soft); letter-spacing: 1.2px; text-transform: uppercase;">MONTHLY RECURRING REVENUE</span>
            <strong id="sub-kpi-mrr" style="display: block; margin: 8px 0 4px; font: 500 28px/1 var(--serif); color: var(--ink);">$0.00</strong>
            <small style="color: var(--ink-soft); font-size: 10px;">Estimated recurring revenue</small>
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="position: absolute; top: 16px; right: 16px; width: 20px; height: 20px; color: var(--terracotta);"><path d="M6 3.5h11.5L21 7v13.5l-3-1.8-3 1.8-3-1.8-3 1.8-3-1.8z"/><path d="M9 10h6M9 14h7"/></svg>
        </article>

        <article class="sub-kpi" style="padding: 16px 20px; border: 1px solid var(--line); border-radius: 10px; background: #ffffff; position: relative;">
            <span style="display: block; font-size: 9px; font-weight: 700; color: var(--ink-soft); letter-spacing: 1.2px; text-transform: uppercase;">PAUSED MEMBERSHIPS</span>
            <strong id="sub-kpi-paused" style="display: block; margin: 8px 0 4px; font: 500 28px/1 var(--serif); color: var(--ink);">0</strong>
            <small style="color: var(--ochre); font-size: 10px; font-weight: 600;">Delivery holds in place</small>
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="position: absolute; top: 16px; right: 16px; width: 20px; height: 20px; color: var(--ochre);"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </article>
    </div>

    <!-- Membership Table Panel -->
    <div class="panel table-panel member-table-panel">
        <div class="table-title-row">
            <div>
                <h2>The membership roster</h2>
                <span>Small-batch deliveries, with room to pause</span>
            </div>
            <button class="button button-secondary" type="button" onclick="window.exportTransactionsCsv()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 14px; height: 14px;"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/></svg>
                <span>Export roster</span>
            </button>
        </div>

        <div class="table-scroll">
            <table id="subscriptions-table" class="data-table member-table">
                <thead>
                    <tr>
                        <th>MEMBER</th>
                        <th>MEMBERSHIP</th>
                        <th>CADENCE / PRICE</th>
                        <th>STATUS</th>
                        <th>NEXT RENEWAL</th>
                        <th style="text-align: right;"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="subscriptions-tbody">
                    @include('partials.admin.components.table-skeleton', ['rows' => 4, 'cols' => 6])
                </tbody>
            </table>
        </div>

        <div class="table-foot" style="display: flex; justify-content: space-between; padding: 14px 20px; border-top: 1px solid var(--line); color: var(--ink-soft); font-size: 11px;">
            <span>Stripe billing schedules synchronized automatically</span>
            <span class="foot-note" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 14px; height: 14px;"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/></svg>
                OFF-SESSION VAULTING
            </span>
        </div>
    </div>

    <div class="page-tip" style="display: flex; align-items: center; gap: 12px; margin-top: 24px; padding: 16px 20px; border-radius: 10px; background: #fffdfa; border: 1px solid var(--line); font-size: 12px; color: var(--ink-soft);">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--terracotta)" stroke-width="1.6" style="width: 20px; height: 20px; flex-shrink: 0;"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/></svg>
        <p style="margin: 0; flex: 1;">
            Every monthly stoneware delivery is unique. Members can pause or skip a season anytime from their private portal link.
        </p>
        <a href="/portal/demo" target="_blank" class="text-link">Open member portal preview →</a>
    </div>
</div>
