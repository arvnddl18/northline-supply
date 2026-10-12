<!-- TAB: ORDERS & PRE-ORDERS -->
<div id="tab-orders" class="tab-content" role="tabpanel" aria-labelledby="tab-btn-orders">
    <div class="section-heading">
        <div>
            <p class="eyebrow">FULFILLMENT &amp; FINANCE</p>
            <h1>Every piece, in good time.</h1>
            <p class="section-copy">Follow each order from its first deposit to the moment it leaves the studio.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="button button-secondary" type="button" onclick="window.loadOrders()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 7v5h-5M5 17v-5h5"/><path d="M6.5 9a6.5 6.5 0 0 1 11-2L19 8M5 16l1.5 1a6.5 6.5 0 0 0 11-2"/>
                </svg>
                <span>Refresh</span>
            </button>
            <button class="button button-secondary" type="button" onclick="window.exportTransactionsCsv()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/>
                </svg>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    <!-- Orders Toolbar -->
    <div class="orders-toolbar" style="display: flex; gap: 12px; margin-bottom: 16px; align-items: center; flex-wrap: wrap;">
        <div class="search-box" style="position: relative; flex: 1; min-width: 280px; display: flex; align-items: center;">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="position: absolute; left: 14px; width: 16px; height: 16px; color: var(--ink-soft); pointer-events: none;">
                <circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4 4"/>
            </svg>
            <input type="search" id="orders-search-input" class="nc-input" style="width: 100%; height: 42px; padding: 0 54px 0 38px; border: 1px solid var(--clay-200); border-radius: 8px; background: #ffffff;" aria-label="Search orders" placeholder="Search order # or customer email…" oninput="window.debounceSearchOrders(this.value)">
            <kbd style="position: absolute; right: 12px; padding: 2px 6px; font-size: 10px; font-family: var(--mono); color: var(--ink-soft); background: var(--clay-100); border-radius: 4px; border: 1px solid var(--clay-200);">⌘ K</kbd>
        </div>
    </div>

    <!-- Filter Scroll Pills -->
    <div class="filter-scroll" id="order-status-filters" role="group" aria-label="Filter orders" style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 18px;">
        <button type="button" class="filter-pill filter-pill-active" data-status="" onclick="window.filterOrderStatus('')">All Orders</button>
        <button type="button" class="filter-pill" data-status="deposit_paid" onclick="window.filterOrderStatus('deposit_paid')">Deposit Paid</button>
        <button type="button" class="filter-pill" data-status="balance_due" onclick="window.filterOrderStatus('balance_due')">Balance Due</button>
        <button type="button" class="filter-pill" data-status="paid" onclick="window.filterOrderStatus('paid')">Paid in Full</button>
        <button type="button" class="filter-pill" data-status="payment_failed" onclick="window.filterOrderStatus('payment_failed')">
            Payment Failed
            <span class="filter-count" id="order-filter-failed-count" style="display: none;">0</span>
        </button>
        <button type="button" class="filter-pill" data-status="refunded" onclick="window.filterOrderStatus('refunded')">Refunded</button>
    </div>

    <!-- Orders Table Panel -->
    <div class="panel table-panel orders-table-panel">
        <div class="table-title-row">
            <div>
                <h2>Orders and payment schedules</h2>
                <span>Most recent first · AUD integer cents</span>
            </div>
            <span class="live-label" style="display: inline-flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 600; color: var(--sage);">
                <i class="signal-dot"></i> LIVE LEDGER
            </span>
        </div>

        <div class="table-scroll">
            <table id="orders-table" class="data-table orders-table">
                <thead>
                    <tr>
                        <th>ORDER</th>
                        <th>CUSTOMER</th>
                        <th>STATUS</th>
                        <th>TOTAL AMOUNT</th>
                        <th>PAID DEPOSIT</th>
                        <th>BALANCE DUE</th>
                        <th style="text-align: right;"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    @include('partials.admin.components.table-skeleton', ['rows' => 5, 'cols' => 7])
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="pagination" id="orders-pagination" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-top: 1px solid var(--line); font-size: 11px;">
            <span id="orders-page-info" style="color: var(--ink-soft);">Showing orders</span>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="button button-secondary" id="orders-page-prev" style="min-height: 32px; padding: 0 12px;" onclick="window.changeOrderPage(-1)" disabled>&larr; Previous</button>
                <button type="button" class="button button-secondary" id="orders-page-next" style="min-height: 32px; padding: 0 12px;" onclick="window.changeOrderPage(1)" disabled>Next &rarr;</button>
            </div>
        </div>
    </div>

    <div class="ledger-summary" style="display: flex; justify-content: space-between; margin-top: 16px; font-size: 11px; color: var(--ink-soft);">
        <span style="display: inline-flex; align-items: center; gap: 6px;">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 14px; height: 14px;"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/></svg>
            PCI SAQ-A scope isolated · Authenticated session
        </span>
        <span>AUD Integer Cents</span>
    </div>
</div>
