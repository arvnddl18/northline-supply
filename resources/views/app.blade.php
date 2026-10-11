<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="shopify-api-key" content="{{ $apiKey }}">
    <meta name="shopify-shop-domain" content="{{ $shop }}">
    <meta name="shopify-host" content="{{ $host }}">
    <title>Northwind Ceramics — Payment Management</title>

    <link rel="preconnect" href="https://cdn.shopify.com">
    <link rel="stylesheet" href="https://cdn.shopify.com/static/fonts/inter/v4/styles.css">
    
    <!-- Shopify App Bridge v4 -->
    <script src="https://cdn.shopify.com/shopify-app-bridge-types/v4/index.js"></script>
    <script src="https://cdn.shopify.com/shopify-app-bridge/latest/index.js" data-api-key="{{ $apiKey }}"></script>

    <style>
        :root {
            --p-color-bg-surface: #ffffff;
            --p-color-bg-surface-secondary: #f7f7f8;
            --p-color-text: #202223;
            --p-color-text-subdued: #6d7175;
            --p-color-border: #e1e3e5;
            --p-color-primary: #008060;
            --p-color-primary-hover: #006e52;
            --p-color-success-bg: #e4f7ed;
            --p-color-success-text: #108043;
            --p-color-warning-bg: #fff5ea;
            --p-color-warning-text: #b95000;
            --p-border-radius-base: 8px;
            --p-font-family-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--p-font-family-sans);
            background-color: #f1f2f4;
            color: var(--p-color-text);
            line-height: 1.5;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }

        .polaris-layout {
            max-width: 1040px;
            margin: 0 auto;
            padding: 24px 16px;
        }

        .polaris-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .polaris-header h1 {
            font-size: 20px;
            font-weight: 600;
            color: var(--p-color-text);
        }

        .polaris-banner {
            background-color: #f0f7ff;
            border: 1px solid #cce5ff;
            border-radius: var(--p-border-radius-base);
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .polaris-banner.success {
            background-color: var(--p-color-success-bg);
            border-color: #bbf0d0;
            color: var(--p-color-success-text);
        }

        .polaris-banner-content h2 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .polaris-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .polaris-grid {
                grid-template-columns: 1fr;
            }
        }

        .polaris-card {
            background: var(--p-color-bg-surface);
            border-radius: var(--p-border-radius-base);
            border: 1px solid var(--p-color-border);
            padding: 20px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .polaris-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .polaris-card-header h3 {
            font-size: 16px;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background-color: var(--p-color-success-bg);
            color: var(--p-color-success-text);
        }

        .badge-warning {
            background-color: var(--p-color-warning-bg);
            color: var(--p-color-warning-text);
        }

        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--p-color-border);
        }

        .checklist-item:last-child {
            border-bottom: none;
        }

        .checklist-item-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checklist-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .checklist-icon.done {
            background-color: var(--p-color-primary);
            color: white;
        }

        .checklist-icon.pending {
            background-color: var(--p-color-border);
            color: var(--p-color-text-subdued);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px; /* WCAG 2.1 touch target */
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: var(--p-color-primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--p-color-primary-hover);
        }

        .btn-secondary {
            background-color: #ffffff;
            border-color: var(--p-color-border);
            color: var(--p-color-text);
        }

        .btn-secondary:hover {
            background-color: var(--p-color-bg-surface-secondary);
        }

        .nav-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid var(--p-color-border);
            margin-bottom: 20px;
        }

        .nav-tab {
            padding: 10px 16px;
            min-height: 44px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            color: var(--p-color-text-subdued);
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
        }

        .nav-tab.active {
            color: var(--p-color-primary);
            border-bottom-color: var(--p-color-primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .badge-danger, .badge-critical {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .filter-pill {
            min-height: 32px;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 13px;
        }

        .filter-pill.active {
            background-color: var(--p-color-primary);
            color: #ffffff;
            border-color: var(--p-color-primary);
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-dialog {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            max-width: 520px;
            width: 90%;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Shopify App Bridge Navigation Sidebar Integration -->
    <ui-nav-menu>
        <a href="/" rel="home">Overview</a>
        <a href="#plans">Payment Plans</a>
        <a href="#orders">Orders</a>
        <a href="#audit">Audit Trail</a>
        <a href="#settings">Settings</a>
    </ui-nav-menu>

    <ui-title-bar title="Northwind Ceramics Payments">
        <button variant="primary" onclick="window.navigateTab('plans')">Configure Plans</button>
    </ui-title-bar>

    <main class="polaris-layout">
        <!-- Breadcrumb & Header -->
        <header class="polaris-header">
            <div>
                <h1>Northwind Ceramics &mdash; Custom Payment App</h1>
                <p style="color: var(--p-color-text-subdued); font-size: 13px;">Shopify &amp; Stripe Pre-order Deposits, Installments &amp; Subscriptions</p>
            </div>
            <div>
                <span class="badge badge-info" id="shop-badge">{{ $shop }}</span>
            </div>
        </header>

        <!-- Navigation Tabs -->
        <nav class="nav-tabs" role="tablist">
            <button class="nav-tab active" role="tab" id="tab-btn-overview" onclick="window.navigateTab('overview')">Overview</button>
            <button class="nav-tab" role="tab" id="tab-btn-plans" onclick="window.navigateTab('plans')">Payment Plans</button>
            <button class="nav-tab" role="tab" id="tab-btn-orders" onclick="window.navigateTab('orders')">Orders</button>
            <button class="nav-tab" role="tab" id="tab-btn-audit" onclick="window.navigateTab('audit')">Audit Trail</button>
            <button class="nav-tab" role="tab" id="tab-btn-settings" onclick="window.navigateTab('settings')">Settings</button>
        </nav>

        <!-- TAB: OVERVIEW -->
        <div id="tab-overview" class="tab-content active">
            <!-- Financial KPIs (S3-T1) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;" id="dashboard-kpis">
                <div class="polaris-card" style="margin-bottom: 0; padding: 16px; border-left: 4px solid #008060; cursor: pointer;" onclick="window.navigateTab('orders'); window.filterOrdersByStatus('paid');">
                    <p style="font-size: 13px; color: var(--p-color-text-subdued); margin-bottom: 6px; font-weight: 500;">TOTAL CASH COLLECTED</p>
                    <h2 id="kpi-collected" style="font-size: 24px; font-weight: 700; color: #008060;">$0.00</h2>
                    <p style="font-size: 12px; color: var(--p-color-text-subdued); margin-top: 4px;">Captured deposits &amp; balances</p>
                </div>
                <div class="polaris-card" style="margin-bottom: 0; padding: 16px; border-left: 4px solid #2c6ecb; cursor: pointer;" onclick="window.navigateTab('orders'); window.filterOrdersByStatus('deposit_paid');">
                    <p style="font-size: 13px; color: var(--p-color-text-subdued); margin-bottom: 6px; font-weight: 500;">PENDING BALANCES DUE</p>
                    <h2 id="kpi-balance-due" style="font-size: 24px; font-weight: 700; color: #2c6ecb;">$0.00</h2>
                    <p style="font-size: 12px; color: var(--p-color-text-subdued); margin-top: 4px;">Outstanding pre-orders in studio</p>
                </div>
                <div class="polaris-card" style="margin-bottom: 0; padding: 16px; border-left: 4px solid #b98900; cursor: pointer;" onclick="window.navigateTab('orders'); window.filterOrdersByStatus('deposit_paid');">
                    <p style="font-size: 13px; color: var(--p-color-text-subdued); margin-bottom: 6px; font-weight: 500;">UPCOMING CHARGES</p>
                    <h2 id="kpi-upcoming" style="font-size: 24px; font-weight: 700; color: #b98900;">0 ($0.00)</h2>
                    <p style="font-size: 12px; color: var(--p-color-text-subdued); margin-top: 4px;">Scheduled kiln-ready payouts</p>
                </div>
                <div class="polaris-card" style="margin-bottom: 0; padding: 16px; border-left: 4px solid #d72c0d; cursor: pointer;" onclick="window.navigateTab('orders'); window.filterOrdersByStatus('failed');">
                    <p style="font-size: 13px; color: var(--p-color-text-subdued); margin-bottom: 6px; font-weight: 500;">FAILED PAYMENTS</p>
                    <h2 id="kpi-failed" style="font-size: 24px; font-weight: 700; color: #d72c0d;">0 ($0.00)</h2>
                    <p style="font-size: 12px; color: var(--p-color-text-subdued); margin-top: 4px;">Requires dunning or retry</p>
                </div>
            </div>

            <!-- Setup Checklist Banner -->
            <div class="polaris-banner" id="checklist-banner">
                <div class="polaris-banner-content">
                    <h2>Setup in progress for Melbourne Studio</h2>
                    <p style="color: var(--p-color-text-subdued);">Complete the onboarding steps below to begin accepting pre-order deposits and scheduled balances.</p>
                </div>
            </div>

            <div class="polaris-grid">
                <div>
                    <!-- Setup Checklist Card -->
                    <div class="polaris-card">
                        <div class="polaris-card-header">
                            <h3>Merchant Onboarding Checklist</h3>
                            <span class="badge badge-warning" id="status-badge">Setup Incomplete</span>
                        </div>
                        <div class="checklist">
                            <div class="checklist-item">
                                <div class="checklist-item-title">
                                    <div class="checklist-icon done">&#10003;</div>
                                    <div>
                                        <strong>1. Shopify App Installation</strong>
                                        <p style="color: var(--p-color-text-subdued); font-size: 13px;">Installed with AES-256 encrypted access token.</p>
                                    </div>
                                </div>
                                <span class="badge badge-success">Completed</span>
                            </div>

                            <div class="checklist-item">
                                <div class="checklist-item-title">
                                    <div class="checklist-icon pending" id="chk-stripe-icon">&#8226;</div>
                                    <div>
                                        <strong>2. Connect Stripe Account</strong>
                                        <p style="color: var(--p-color-text-subdued); font-size: 13px;">Enable Stripe Checkout, PaymentIntents and AUD settlement.</p>
                                    </div>
                                </div>
                                <button class="btn btn-secondary" style="min-height: 36px; padding: 4px 12px;" onclick="window.navigateTab('settings')">Connect</button>
                            </div>

                            <div class="checklist-item">
                                <div class="checklist-item-title">
                                    <div class="checklist-icon pending" id="chk-plan-icon">&#8226;</div>
                                    <div>
                                        <strong>3. Create Deposit Plan</strong>
                                        <p style="color: var(--p-color-text-subdued); font-size: 13px;">Define percentage (e.g. 30%) or fixed deposit on ceramic items.</p>
                                    </div>
                                </div>
                                <button class="btn btn-secondary" style="min-height: 36px; padding: 4px 12px;" onclick="window.navigateTab('plans')">Add Plan</button>
                            </div>

                            <div class="checklist-item">
                                <div class="checklist-item-title">
                                    <div class="checklist-icon pending">&#8226;</div>
                                    <div>
                                        <strong>4. Enable Storefront Widget</strong>
                                        <p style="color: var(--p-color-text-subdued); font-size: 13px;">Activate the Theme App Extension block on your product pages.</p>
                                    </div>
                                </div>
                                <span class="badge badge-warning">Pending</span>
                            </div>
                        </div>
                    </div>

                    <!-- App Specifications Summary -->
                    <div class="polaris-card">
                        <div class="polaris-card-header">
                            <h3>Active System Configuration</h3>
                        </div>
                        <p style="margin-bottom: 12px; color: var(--p-color-text-subdued);">Architected for handmade, made-to-order ceramics with 3-6 week lead times.</p>
                        <ul style="padding-left: 20px; line-height: 1.8; color: var(--p-color-text);">
                            <li><strong>Currency:</strong> {{ $currency }} (Integer cents precision)</li>
                            <li><strong>Checkout Architecture:</strong> Option B &mdash; Stripe-First via Shopify App Proxy</li>
                            <li><strong>PCI DSS Scope:</strong> SAQ-A compliant (Zero raw card numbers stored)</li>
                            <li><strong>Webhook Ingestion:</strong> Sub-second queue dispatch with Redis locks</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <!-- Quick Actions Card -->
                    <div class="polaris-card">
                        <div class="polaris-card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <button class="btn btn-primary" onclick="window.navigateTab('plans')">Create New Plan</button>
                            <button class="btn btn-secondary" onclick="window.navigateTab('settings')">Stripe Settings</button>
                            <button class="btn btn-secondary" onclick="window.refreshOverview()">Refresh Status</button>
                        </div>
                    </div>

                    <!-- Client Profile Card -->
                    <div class="polaris-card">
                        <div class="polaris-card-header">
                            <h3>Store Information</h3>
                        </div>
                        <p><strong>Merchant:</strong> Emma Carter</p>
                        <p><strong>Location:</strong> Melbourne, Australia</p>
                        <p><strong>Platform:</strong> Shopify Admin Embedded (Polaris + App Bridge v4)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: PAYMENT PLANS -->
        <div id="tab-plans" class="tab-content">
            <div class="polaris-card">
                <div class="polaris-card-header">
                    <div>
                        <h3>Deposit &amp; Installment Plans</h3>
                        <p style="color: var(--p-color-text-subdued); font-size: 13px; margin-top: 4px;">Configure deposit percentages or fixed amounts applied to specific products or collections.</p>
                    </div>
                    <button class="btn btn-primary" onclick="window.openPlanModal()">+ Create New Plan</button>
                </div>

                <!-- Plan Alert Banner -->
                <div id="plan-alert" style="display: none; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px;"></div>

                <!-- Plans Table / List Container -->
                <div id="plans-table-container">
                    <div style="background: var(--p-color-bg-surface-secondary); padding: 32px; text-align: center; border-radius: 6px;" id="plans-empty-state">
                        <p style="font-weight: 500; margin-bottom: 8px;">No plans configured yet</p>
                        <p style="color: var(--p-color-text-subdued); font-size: 13px; margin-bottom: 16px;">Create a deposit plan to allow pre-orders on handmade ceramic products.</p>
                        <button class="btn btn-primary" onclick="window.openPlanModal()">Create First Plan</button>
                    </div>
                    <table id="plans-table" style="display: none; width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--p-color-border); color: var(--p-color-text-subdued); font-size: 12px; text-transform: uppercase;">
                                <th style="padding: 10px 12px;">Plan Name</th>
                                <th style="padding: 10px 12px;">Type</th>
                                <th style="padding: 10px 12px;">Deposit Amount</th>
                                <th style="padding: 10px 12px;">Target Scope</th>
                                <th style="padding: 10px 12px;">Status</th>
                                <th style="padding: 10px 12px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="plans-tbody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Plan Form Modal / Card -->
            <div class="polaris-card" id="plan-editor-card" style="display: none; margin-top: 20px; border: 2px solid var(--p-color-primary);">
                <div class="polaris-card-header">
                    <h3 id="plan-editor-title">Create Deposit Plan</h3>
                    <button class="btn btn-secondary" style="min-height: 32px; padding: 2px 10px;" onclick="window.closePlanModal()">&times; Close</button>
                </div>

                <form id="plan-form" onsubmit="event.preventDefault(); window.savePlan();" style="display: flex; flex-direction: column; gap: 16px;">
                    <input type="hidden" id="plan-edit-id" value="">

                    <div>
                        <label for="plan-name-input" style="display: block; font-weight: 500; margin-bottom: 6px;">Plan Title</label>
                        <input type="text" id="plan-name-input" placeholder="e.g. 30% Made-to-Order Ceramic Deposit" required style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 14px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label for="plan-deposit-type" style="display: block; font-weight: 500; margin-bottom: 6px;">Deposit Calculation Method</label>
                            <select id="plan-deposit-type" onchange="window.updatePlanPreview()" style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 14px; background: white;">
                                <option value="percentage">Percentage (e.g. 30%)</option>
                                <option value="fixed">Fixed Cents Amount (e.g. $50.00 AUD = 5000 cents)</option>
                            </select>
                        </div>
                        <div>
                            <label for="plan-deposit-value" style="display: block; font-weight: 500; margin-bottom: 6px;">Deposit Value</label>
                            <input type="number" id="plan-deposit-value" value="30" min="1" required oninput="window.updatePlanPreview()" style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 14px;">
                            <span id="plan-deposit-value-hint" style="font-size: 12px; color: var(--p-color-text-subdued); margin-top: 4px; display: block;">Enter percentage (1–99).</span>
                        </div>
                    </div>

                    <!-- Live Calculation Preview Card -->
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 14px 18px;">
                        <h4 style="margin: 0 0 6px 0; color: #166534; font-size: 14px;">Live Calculation Preview (Sample $200.00 AUD Item)</h4>
                        <div style="display: flex; gap: 24px; font-size: 13px;">
                            <div>
                                <span style="color: #4b5563;">Due Upfront (Deposit):</span>
                                <strong id="preview-deposit-label" style="color: #166534; margin-left: 6px; font-size: 15px;">$60.00 AUD</strong>
                            </div>
                            <div>
                                <span style="color: #4b5563;">Due Before Delivery (Balance):</span>
                                <strong id="preview-balance-label" style="color: #1f2937; margin-left: 6px; font-size: 15px;">$140.00 AUD</strong>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="plan-product-targets" style="display: block; font-weight: 500; margin-bottom: 6px;">Specific Product IDs (Optional, comma-separated)</label>
                        <input type="text" id="plan-product-targets" placeholder="e.g. gid://shopify/Product/1001, gid://shopify/Product/1002" style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 13px; font-family: monospace;">
                        <span style="font-size: 12px; color: var(--p-color-text-subdued); margin-top: 4px; display: block;">Leave blank to apply to all products unless collection is specified.</span>
                    </div>

                    <div>
                        <label for="plan-collection-targets" style="display: block; font-weight: 500; margin-bottom: 6px;">Specific Collection IDs (Optional, comma-separated)</label>
                        <input type="text" id="plan-collection-targets" placeholder="e.g. gid://shopify/Collection/5001" style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 13px; font-family: monospace;">
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="plan-active-input" checked style="width: 18px; height: 18px;">
                        <label for="plan-active-input" style="font-size: 14px; font-weight: 500;">Active and eligible for storefront deposit checkout</label>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 8px;">
                        <button type="submit" class="btn btn-primary" id="btn-save-plan">Save Plan</button>
                        <button type="button" class="btn btn-secondary" onclick="window.closePlanModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB: ORDERS -->
        <div id="tab-orders" class="tab-content">
            <div class="polaris-card">
                <div class="polaris-card-header">
                    <div>
                        <h3>Order Ledger &amp; Balance Schedules</h3>
                        <p style="color: var(--p-color-text-subdued); font-size: 13px; margin-top: 4px;">Track handmade ceramic pre-orders, deposit settlements, balance payment links, and refunds.</p>
                    </div>
                    <button class="btn btn-secondary" onclick="window.loadOrders()">&#8635; Refresh Orders</button>
                </div>

                <!-- Order Feedback Alert -->
                <div id="order-alert" style="display: none; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px;"></div>

                <!-- Filters & Search Toolbar (S3-T2) -->
                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;" id="order-status-filters">
                        <button class="btn btn-secondary filter-pill active" onclick="window.filterOrdersByStatus('')">All</button>
                        <button class="btn btn-secondary filter-pill" onclick="window.filterOrdersByStatus('deposit_paid')">Deposit Paid</button>
                        <button class="btn btn-secondary filter-pill" onclick="window.filterOrdersByStatus('paid')">Paid in Full</button>
                        <button class="btn btn-secondary filter-pill" onclick="window.filterOrdersByStatus('partially_refunded')">Partially Refunded</button>
                        <button class="btn btn-secondary filter-pill" onclick="window.filterOrdersByStatus('refunded')">Refunded</button>
                        <button class="btn btn-secondary filter-pill" onclick="window.filterOrdersByStatus('failed')">Payment Failed</button>
                    </div>
                    <div style="min-width: 260px; flex: 1; max-width: 360px;">
                        <input type="text" id="order-search-input" placeholder="Search order # or customer email..." oninput="window.debounceOrderSearch()" style="width: 100%; padding: 8px 12px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 13px;">
                    </div>
                </div>

                <!-- Orders Table Container -->
                <div id="orders-table-container">
                    <div style="background: var(--p-color-bg-surface-secondary); padding: 32px; text-align: center; border-radius: 6px; display: none;" id="orders-empty-state">
                        <p style="font-weight: 500; margin-bottom: 8px;">No orders found</p>
                        <p style="color: var(--p-color-text-subdued); font-size: 13px;">Try adjusting your search criteria or status filter.</p>
                    </div>
                    <table id="orders-table" style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--p-color-border); color: var(--p-color-text-subdued); font-size: 12px; text-transform: uppercase;">
                                <th style="padding: 10px 12px;">Order #</th>
                                <th style="padding: 10px 12px;">Customer</th>
                                <th style="padding: 10px 12px;">Status</th>
                                <th style="padding: 10px 12px;">Total</th>
                                <th style="padding: 10px 12px;">Paid</th>
                                <th style="padding: 10px 12px;">Balance Due</th>
                                <th style="padding: 10px 12px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="orders-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Polaris Refund Modal Dialog (S3-T3) -->
        <div id="refund-modal" class="modal-backdrop" style="display: none;">
            <div class="modal-dialog">
                <div style="padding: 20px; border-bottom: 1px solid var(--p-color-border); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px;">Issue Refund via Stripe</h3>
                    <button type="button" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--p-color-text-subdued);" onclick="window.closeRefundModal()">&times;</button>
                </div>
                <form id="refund-form" onsubmit="event.preventDefault(); window.submitRefund();" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <input type="hidden" id="refund-order-id" value="">
                    
                    <div style="background: var(--p-color-bg-surface-secondary); padding: 12px 16px; border-radius: 6px; font-size: 13px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span>Shopify Order:</span>
                            <strong id="refund-modal-order-number">#---</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span>Customer:</span>
                            <span id="refund-modal-customer">---</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span>Total Paid:</span>
                            <strong id="refund-modal-paid">$0.00 AUD</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span>Already Refunded:</span>
                            <span id="refund-modal-refunded">$0.00 AUD</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 600; color: #008060; border-top: 1px solid var(--p-color-border); padding-top: 6px; margin-top: 6px;">
                            <span>Max Refundable:</span>
                            <span id="refund-modal-max">$0.00 AUD</span>
                        </div>
                    </div>

                    <div>
                        <label for="refund-amount-input" style="display: block; font-weight: 500; margin-bottom: 6px;">Refund Amount (AUD Dollars, e.g. 50.00)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 10px; top: 10px; color: var(--p-color-text-subdued);">$</span>
                            <input type="number" step="0.01" min="0.01" id="refund-amount-input" required style="width: 100%; padding: 10px 10px 10px 24px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 14px;" placeholder="0.00">
                        </div>
                        <div style="margin-top: 4px; display: flex; gap: 8px;">
                            <button type="button" class="btn btn-secondary" style="min-height: 28px; padding: 2px 8px; font-size: 11px;" onclick="window.setFullRefund()">Full Refund</button>
                        </div>
                    </div>

                    <div>
                        <label for="refund-reason-select" style="display: block; font-weight: 500; margin-bottom: 6px;">Reason</label>
                        <select id="refund-reason-select" style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-size: 14px; background: white;">
                            <option value="Customer requested refund">Customer requested refund</option>
                            <option value="Defective ceramic glaze / Kiln flaw">Defective ceramic glaze / Kiln flaw</option>
                            <option value="Lead time exceeded / Cancelled order">Lead time exceeded / Cancelled order</option>
                            <option value="Duplicate order payment">Duplicate order payment</option>
                            <option value="Fraudulent charge">Fraudulent charge</option>
                        </select>
                    </div>

                    <div style="background: #fef3c7; border: 1px solid #fde68a; color: #92400e; padding: 10px 12px; border-radius: 6px; font-size: 12px; line-height: 1.4;">
                        <strong>Note:</strong> Refunding a deposit will automatically cancel any pending balance payment schedules on this order.
                    </div>

                    <div id="refund-modal-error" style="display: none; padding: 8px 12px; border-radius: 6px; background-color: #fee2e2; color: #b91c1c; font-size: 13px;"></div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px;">
                        <button type="button" class="btn btn-secondary" onclick="window.closeRefundModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-refund" style="background-color: #d72c0d; border-color: #d72c0d;">Process Refund</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB: AUDIT TRAIL (S3-T5) -->
        <div id="tab-audit" class="tab-content">
            <div class="polaris-card">
                <div class="polaris-card-header">
                    <div>
                        <h3>Merchant Administrative Audit Trail</h3>
                        <p style="color: var(--p-color-text-subdued); font-size: 13px; margin-top: 4px;">Tamper-evident log of financial refunds, payment plan adjustments, and Stripe credentials (US-24, S3-T5).</p>
                    </div>
                    <button class="btn btn-secondary" onclick="window.loadAuditLogs()">&#8635; Refresh Audit Trail</button>
                </div>

                <div id="audit-table-container">
                    <div style="background: var(--p-color-bg-surface-secondary); padding: 32px; text-align: center; border-radius: 6px; display: none;" id="audit-empty-state">
                        <p style="font-weight: 500; margin-bottom: 8px;">No audit events recorded yet</p>
                        <p style="color: var(--p-color-text-subdued); font-size: 13px;">Administrative actions will automatically generate immutable log entries here.</p>
                    </div>
                    <table id="audit-table" style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--p-color-border); color: var(--p-color-text-subdued); font-size: 12px; text-transform: uppercase;">
                                <th style="padding: 10px 12px;">Timestamp (UTC)</th>
                                <th style="padding: 10px 12px;">Action</th>
                                <th style="padding: 10px 12px;">Resource</th>
                                <th style="padding: 10px 12px;">Actor</th>
                                <th style="padding: 10px 12px;">Details</th>
                            </tr>
                        </thead>
                        <tbody id="audit-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: SETTINGS -->
        <div id="tab-settings" class="tab-content">
            <div class="polaris-card">
                <div class="polaris-card-header">
                    <h3>Stripe Account &amp; API Credentials</h3>
                    <div>
                        <span class="badge badge-warning" id="settings-stripe-badge">Disconnected</span>
                        <span class="badge badge-info" id="settings-stripe-mode" style="display: none; margin-left: 6px;">Test Mode</span>
                    </div>
                </div>
                <p style="color: var(--p-color-text-subdued); margin-bottom: 16px;">Direct Stripe integration for Emma Carter / Northwind Ceramics. Keys are AES-256 encrypted at rest (PCI SAQ-A scope).</p>

                <!-- Feedback banner -->
                <div id="stripe-alert" style="display: none; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px;"></div>

                <div id="stripe-connected-details" style="display: none; background: var(--p-color-bg-surface-secondary); padding: 16px; border-radius: 6px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div>
                            <span style="font-size: 13px; color: var(--p-color-text-subdued);">Connected Account:</span>
                            <strong id="stripe-account-id-label" style="font-family: monospace; margin-left: 6px;">&mdash;</strong>
                        </div>
                        <button class="btn btn-secondary" style="min-height: 36px; padding: 4px 12px; color: #d72c0d; border-color: #d72c0d;" onclick="window.disconnectStripe()">Disconnect</button>
                    </div>
                    <div>
                        <span style="font-size: 13px; color: var(--p-color-text-subdued);">Publishable Key:</span>
                        <code id="stripe-masked-pubkey" style="margin-left: 6px;">&mdash;</code>
                    </div>
                </div>

                <form id="stripe-connect-form" onsubmit="event.preventDefault(); window.connectStripe();" style="display: flex; flex-direction: column; gap: 16px; max-width: 540px;">
                    <div>
                        <label for="stripe-pub-key" style="display: block; font-weight: 500; margin-bottom: 6px;">Stripe Publishable Key</label>
                        <input type="text" id="stripe-pub-key" placeholder="pk_test_..." required style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-family: monospace; font-size: 13px;">
                    </div>

                    <div>
                        <label for="stripe-sec-key" style="display: block; font-weight: 500; margin-bottom: 6px;">Stripe Secret Key</label>
                        <input type="password" id="stripe-sec-key" placeholder="sk_test_..." required style="width: 100%; padding: 10px; border: 1px solid var(--p-color-border); border-radius: 6px; font-family: monospace; font-size: 13px;">
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 4px;">
                        <button type="submit" class="btn btn-primary" id="btn-save-stripe">Connect &amp; Test Credentials</button>
                        <button type="button" class="btn btn-secondary" onclick="window.loadStripeStatus()">Refresh Status</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Embedded Shell Logic & App Bridge Initialization -->
    <script>
        (function() {
            window.navigateTab = function(tabName) {
                document.querySelectorAll('.nav-tab').forEach(function(el) { el.classList.remove('active'); });
                document.querySelectorAll('.tab-content').forEach(function(el) { el.classList.remove('active'); });

                var btn = document.getElementById('tab-btn-' + tabName);
                var content = document.getElementById('tab-' + tabName);
                if (btn) btn.classList.add('active');
                if (content) content.classList.add('active');
            };

            window.addEventListener('hashchange', function() {
                var hash = window.location.hash.replace('#', '');
                if (hash && ['overview', 'plans', 'orders', 'audit', 'settings'].includes(hash)) {
                    window.navigateTab(hash);
                }
            });

            async function getAuthHeaders() {
                var headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                };
                if (window.shopify && typeof window.shopify.idToken === 'function') {
                    var token = await window.shopify.idToken();
                    if (token) {
                        headers['Authorization'] = 'Bearer ' + token;
                    }
                }
                return headers;
            }

            function showStripeAlert(message, isSuccess) {
                var el = document.getElementById('stripe-alert');
                el.style.display = 'block';
                el.innerText = message;
                el.style.backgroundColor = isSuccess ? '#ecfdf5' : '#fef2f2';
                el.style.color = isSuccess ? '#065f46' : '#991b1b';
                el.style.border = isSuccess ? '1px solid #a7f3d0' : '1px solid #fecaca';
            }

            window.loadStripeStatus = async function() {
                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/stripe/status', { headers: headers });
                    if (res.ok) {
                        var data = await res.json();
                        var badge = document.getElementById('settings-stripe-badge');
                        var modeBadge = document.getElementById('settings-stripe-mode');
                        var detailsBox = document.getElementById('stripe-connected-details');
                        var acctLabel = document.getElementById('stripe-account-id-label');
                        var maskedPub = document.getElementById('stripe-masked-pubkey');

                        if (data.connected) {
                            badge.className = 'badge badge-success';
                            badge.innerText = 'Connected';
                            modeBadge.style.display = 'inline-block';
                            modeBadge.innerText = data.test_mode ? 'Test Mode' : 'Live Mode';
                            modeBadge.className = data.test_mode ? 'badge badge-warning' : 'badge badge-success';
                            detailsBox.style.display = 'block';
                            acctLabel.innerText = data.stripe_account_id || '(Direct API Key)';
                            maskedPub.innerText = data.publishable_key || 'configured';
                        } else {
                            badge.className = 'badge badge-warning';
                            badge.innerText = 'Disconnected';
                            modeBadge.style.display = 'none';
                            detailsBox.style.display = 'none';
                        }
                    }
                } catch (e) {
                    console.log('Stripe status fetch failed:', e);
                }
            };

            window.connectStripe = async function() {
                var btn = document.getElementById('btn-save-stripe');
                var pubKey = document.getElementById('stripe-pub-key').value;
                var secKey = document.getElementById('stripe-sec-key').value;

                btn.disabled = true;
                btn.innerText = 'Verifying with Stripe...';

                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/stripe/connect', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({
                            stripe_publishable_key: pubKey,
                            stripe_secret_key: secKey
                        })
                    });

                    var result = await res.json();
                    if (res.ok) {
                        showStripeAlert('Stripe account verified and securely connected.', true);
                        document.getElementById('stripe-sec-key').value = '';
                        await window.loadStripeStatus();
                        await window.refreshOverview();
                    } else {
                        showStripeAlert(result.error || 'Failed to connect Stripe account.', false);
                    }
                } catch (e) {
                    showStripeAlert('Network error connecting to Stripe API.', false);
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Connect & Test Credentials';
                }
            };

            window.disconnectStripe = async function() {
                if (!confirm('Are you sure you want to disconnect your Stripe account?')) return;

                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/stripe/disconnect', {
                        method: 'POST',
                        headers: headers
                    });

                    if (res.ok) {
                        showStripeAlert('Stripe account disconnected.', true);
                        await window.loadStripeStatus();
                        await window.refreshOverview();
                    }
                } catch (e) {
                    showStripeAlert('Network error disconnecting Stripe.', false);
                }
            };

            function showPlanAlert(message, isSuccess) {
                var el = document.getElementById('plan-alert');
                el.style.display = 'block';
                el.innerText = message;
                el.style.backgroundColor = isSuccess ? '#ecfdf5' : '#fef2f2';
                el.style.color = isSuccess ? '#065f46' : '#991b1b';
                el.style.border = isSuccess ? '1px solid #a7f3d0' : '1px solid #fecaca';
                setTimeout(function() { el.style.display = 'none'; }, 6000);
            }

            window.loadPlans = async function() {
                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/plans', { headers: headers });
                    if (res.ok) {
                        var data = await res.json();
                        var emptyState = document.getElementById('plans-empty-state');
                        var table = document.getElementById('plans-table');
                        var tbody = document.getElementById('plans-tbody');

                        if (!data.plans || data.plans.length === 0) {
                            emptyState.style.display = 'block';
                            table.style.display = 'none';
                            tbody.innerHTML = '';
                        } else {
                            emptyState.style.display = 'none';
                            table.style.display = 'table';
                            tbody.innerHTML = '';

                            data.plans.forEach(function(plan) {
                                var tr = document.createElement('tr');
                                tr.style.borderBottom = '1px solid var(--p-color-border)';

                                var statusBadge = plan.active
                                    ? '<span class="badge badge-success">Active</span>'
                                    : '<span class="badge badge-warning">Paused</span>';

                                var targetDesc = plan.targets_count > 0
                                    ? (plan.targets_count + ' target' + (plan.targets_count > 1 ? 's' : ''))
                                    : 'All Products';

                                tr.innerHTML = `
                                    <td style="padding: 12px;"><strong>${plan.name}</strong></td>
                                    <td style="padding: 12px; text-transform: capitalize;">${plan.type}</td>
                                    <td style="padding: 12px;"><span class="badge badge-info">${plan.formatted_deposit_value}</span></td>
                                    <td style="padding: 12px; color: var(--p-color-text-subdued);">${targetDesc}</td>
                                    <td style="padding: 12px;">${statusBadge}</td>
                                    <td style="padding: 12px; text-align: right;">
                                        <button class="btn btn-secondary" style="min-height: 32px; padding: 2px 8px; font-size: 12px;" onclick="window.openPlanModal(${plan.id})">Edit</button>
                                        <button class="btn btn-secondary" style="min-height: 32px; padding: 2px 8px; font-size: 12px; color: #d72c0d; margin-left: 4px;" onclick="window.deletePlan(${plan.id})">Delete</button>
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                        }
                    }
                } catch (e) {
                    console.log('Error loading plans:', e);
                }
            };

            window.openPlanModal = async function(planId) {
                var card = document.getElementById('plan-editor-card');
                var title = document.getElementById('plan-editor-title');
                card.style.display = 'block';

                if (planId) {
                    title.innerText = 'Edit Payment Plan';
                    document.getElementById('plan-edit-id').value = planId;
                    try {
                        var headers = await getAuthHeaders();
                        var res = await fetch('/api/plans/' + planId, { headers: headers });
                        if (res.ok) {
                            var data = await res.json();
                            var plan = data.plan;
                            document.getElementById('plan-name-input').value = plan.name;
                            document.getElementById('plan-deposit-type').value = plan.deposit_type;
                            document.getElementById('plan-deposit-value').value = plan.deposit_value;
                            document.getElementById('plan-active-input').checked = !!plan.active;

                            var prodTargets = (plan.targets || [])
                                .filter(function(t) { return !!t.shopify_product_id; })
                                .map(function(t) { return t.shopify_product_id; })
                                .join(', ');
                            var collTargets = (plan.targets || [])
                                .filter(function(t) { return !!t.shopify_collection_id; })
                                .map(function(t) { return t.shopify_collection_id; })
                                .join(', ');

                            document.getElementById('plan-product-targets').value = prodTargets;
                            document.getElementById('plan-collection-targets').value = collTargets;
                        }
                    } catch (e) {
                        console.log('Error loading plan details:', e);
                    }
                } else {
                    title.innerText = 'Create Deposit Plan';
                    document.getElementById('plan-edit-id').value = '';
                    document.getElementById('plan-name-input').value = '30% Custom Ceramic Vase Pre-order';
                    document.getElementById('plan-deposit-type').value = 'percentage';
                    document.getElementById('plan-deposit-value').value = '30';
                    document.getElementById('plan-product-targets').value = '';
                    document.getElementById('plan-collection-targets').value = '';
                    document.getElementById('plan-active-input').checked = true;
                }
                window.updatePlanPreview();
                card.scrollIntoView({ behavior: 'smooth' });
            };

            window.closePlanModal = function() {
                document.getElementById('plan-editor-card').style.display = 'none';
            };

            window.updatePlanPreview = async function() {
                var depType = document.getElementById('plan-deposit-type').value;
                var depVal = parseInt(document.getElementById('plan-deposit-value').value, 10);
                var hint = document.getElementById('plan-deposit-value-hint');

                if (depType === 'percentage') {
                    hint.innerText = 'Enter percentage (1–99). E.g. 30 = 30%.';
                } else {
                    hint.innerText = 'Enter fixed amount in whole cents. E.g. 5000 = $50.00 AUD.';
                }

                if (!depVal || depVal <= 0) return;

                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/plans/preview', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({
                            deposit_type: depType,
                            deposit_value: depVal,
                            sample_total_cents: 20000
                        })
                    });
                    if (res.ok) {
                        var data = await res.json();
                        document.getElementById('preview-deposit-label').innerText = data.formatted_deposit;
                        document.getElementById('preview-balance-label').innerText = data.formatted_balance;
                    }
                } catch (e) {
                    console.log('Preview calculation error:', e);
                }
            };

            window.savePlan = async function() {
                var editId = document.getElementById('plan-edit-id').value;
                var name = document.getElementById('plan-name-input').value;
                var depType = document.getElementById('plan-deposit-type').value;
                var depVal = parseInt(document.getElementById('plan-deposit-value').value, 10);
                var active = document.getElementById('plan-active-input').checked;

                var prodStr = document.getElementById('plan-product-targets').value;
                var collStr = document.getElementById('plan-collection-targets').value;

                var productIds = prodStr ? prodStr.split(',').map(function(s) { return s.trim(); }).filter(Boolean) : [];
                var collectionIds = collStr ? collStr.split(',').map(function(s) { return s.trim(); }).filter(Boolean) : [];

                var payload = {
                    name: name,
                    type: 'deposit',
                    deposit_type: depType,
                    deposit_value: depVal,
                    active: active,
                    product_ids: productIds,
                    collection_ids: collectionIds
                };

                var url = editId ? ('/api/plans/' + editId) : '/api/plans';
                var method = editId ? 'PUT' : 'POST';

                var btn = document.getElementById('btn-save-plan');
                btn.disabled = true;

                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch(url, {
                        method: method,
                        headers: headers,
                        body: JSON.stringify(payload)
                    });
                    var result = await res.json();
                    if (res.ok) {
                        showPlanAlert(editId ? 'Plan updated successfully.' : 'Plan created successfully.', true);
                        window.closePlanModal();
                        await window.loadPlans();
                        await window.refreshOverview();
                    } else {
                        var errMsg = result.message || (result.errors ? Object.values(result.errors).flat().join(' ') : 'Failed to save plan.');
                        showPlanAlert(errMsg, false);
                    }
                } catch (e) {
                    showPlanAlert('Network error saving plan.', false);
                } finally {
                    btn.disabled = false;
                }
            };

            window.deletePlan = async function(planId) {
                if (!confirm('Are you sure you want to delete this payment plan?')) return;

                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/plans/' + planId, {
                        method: 'DELETE',
                        headers: headers
                    });

                    if (res.ok) {
                        showPlanAlert('Plan deleted.', true);
                        await window.loadPlans();
                        await window.refreshOverview();
                    }
                } catch (e) {
                    showPlanAlert('Network error deleting plan.', false);
                }
            };

            window.refreshOverview = async function() {
                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/overview', { headers: headers });
                    if (res.ok) {
                        var data = await res.json();
                        if (data.checklist) {
                            if (data.checklist.stripe_connected) {
                                document.getElementById('chk-stripe-icon').className = 'checklist-icon done';
                                document.getElementById('chk-stripe-icon').innerHTML = '&#10003;';
                            }
                            if (data.checklist.plans_configured) {
                                document.getElementById('chk-plan-icon').className = 'checklist-icon done';
                                document.getElementById('chk-plan-icon').innerHTML = '&#10003;';
                            }
                        }
                    }
                } catch (e) {
                    console.log('App Bridge session bootstrap offline:', e);
                }
                await window.loadDashboard();
            };

            var currentOrderStatus = '';
            var currentOrderSearch = '';
            var searchDebounceTimer = null;
            window._activeRefundOrder = null;

            function formatAud(cents) {
                return '$' + (Number(cents || 0) / 100).toFixed(2) + ' AUD';
            }

            window.loadDashboard = async function() {
                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/dashboard', { headers: headers });
                    if (res.ok) {
                        var data = await res.json();
                        var kpis = data.kpis || {};

                        var colEl = document.getElementById('kpi-collected');
                        if (colEl) colEl.innerText = '$' + (Number(kpis.total_collected_cents || 0) / 100).toFixed(2);

                        var dueEl = document.getElementById('kpi-balance-due');
                        if (dueEl) dueEl.innerText = '$' + (Number(kpis.pending_balance_due_cents || 0) / 100).toFixed(2);

                        var upEl = document.getElementById('kpi-upcoming');
                        var upData = kpis.upcoming_scheduled_charges || { count: 0, total_cents: 0 };
                        if (upEl) upEl.innerText = upData.count + ' ($' + (Number(upData.total_cents || 0) / 100).toFixed(2) + ')';

                        var failEl = document.getElementById('kpi-failed');
                        var failData = kpis.failed_payments || { count: 0, total_cents: 0 };
                        if (failEl) failEl.innerText = failData.count + ' ($' + (Number(failData.total_cents || 0) / 100).toFixed(2) + ')';
                    }
                } catch (e) {
                    console.log('Error loading dashboard KPIs:', e);
                }
            };

            window.filterOrdersByStatus = function(status) {
                currentOrderStatus = status;
                document.querySelectorAll('#order-status-filters .filter-pill').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                if (window.event && window.event.target) {
                    window.event.target.classList.add('active');
                }
                window.loadOrders();
            };

            window.debounceOrderSearch = function() {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(function() {
                    currentOrderSearch = document.getElementById('order-search-input').value.trim();
                    window.loadOrders();
                }, 300);
            };

            function showOrderAlert(msg, isSuccess) {
                var el = document.getElementById('order-alert');
                if (!el) return;
                el.style.display = 'block';
                el.innerText = msg;
                el.style.backgroundColor = isSuccess ? '#ecfdf5' : '#fef2f2';
                el.style.color = isSuccess ? '#065f46' : '#991b1b';
                el.style.border = isSuccess ? '1px solid #a7f3d0' : '1px solid #fecaca';
                setTimeout(function() { el.style.display = 'none'; }, 6000);
            }

            window.loadOrders = async function() {
                try {
                    var headers = await getAuthHeaders();
                    var url = '/api/orders?';
                    if (currentOrderStatus) url += 'status=' + encodeURIComponent(currentOrderStatus) + '&';
                    if (currentOrderSearch) url += 'query=' + encodeURIComponent(currentOrderSearch);

                    var res = await fetch(url, { headers: headers });
                    if (res.ok) {
                        var json = await res.json();
                        var orders = json.data || json || [];
                        var tbody = document.getElementById('orders-tbody');
                        var emptyState = document.getElementById('orders-empty-state');
                        var table = document.getElementById('orders-table');

                        if (!tbody || !emptyState || !table) return;

                        if (!orders || orders.length === 0) {
                            tbody.innerHTML = '';
                            table.style.display = 'none';
                            emptyState.style.display = 'block';
                            return;
                        }

                        table.style.display = 'table';
                        emptyState.style.display = 'none';
                        tbody.innerHTML = '';

                        orders.forEach(function(o) {
                            var tr = document.createElement('tr');
                            tr.style.borderBottom = '1px solid var(--p-color-border)';

                            var badgeClass = 'badge-info';
                            if (o.payment_status === 'paid') badgeClass = 'badge-success';
                            else if (o.payment_status === 'deposit_paid') badgeClass = 'badge-info';
                            else if (o.payment_status === 'failed') badgeClass = 'badge-critical';
                            else if (o.payment_status === 'refunded') badgeClass = 'badge-critical';
                            else if (o.payment_status === 'partially_refunded' || o.payment_status === 'pending_deposit') badgeClass = 'badge-warning';

                            var refundableCents = Math.max(0, (o.paid_amount || 0) - (o.total_refunded || 0));

                            var actions = [];
                            if (o.payment_status === 'deposit_paid' || (o.remaining_balance > 0 && o.payment_status !== 'refunded')) {
                                actions.push('<button class="btn btn-secondary" style="min-height: 30px; padding: 2px 8px; font-size: 12px;" onclick="window.copyPaymentLink(' + o.id + ')">Copy Link</button>');
                            }

                            if (refundableCents > 0) {
                                var safeOrder = encodeURIComponent(JSON.stringify(o));
                                actions.push('<button class="btn btn-secondary" style="min-height: 30px; padding: 2px 8px; font-size: 12px; color: #d72c0d; border-color: #fca5a5;" onclick="window.openRefundModalDecoded(\'' + safeOrder + '\')">Refund</button>');
                            }

                            tr.innerHTML = `
                                <td style="padding: 12px;"><strong>#${o.shopify_order_id || o.id}</strong><div style="font-size: 12px; color: var(--p-color-text-subdued);">${o.plan_name || ''}</div></td>
                                <td style="padding: 12px;">${o.customer_email || '—'}</td>
                                <td style="padding: 12px;"><span class="badge ${badgeClass}">${o.status_label || o.payment_status}</span></td>
                                <td style="padding: 12px;">${formatAud(o.total_amount)}</td>
                                <td style="padding: 12px; color: #008060; font-weight: 500;">${formatAud(o.paid_amount)}</td>
                                <td style="padding: 12px; color: ${o.remaining_balance > 0 ? '#b98900' : 'inherit'}; font-weight: ${o.remaining_balance > 0 ? '600' : 'normal'};">${formatAud(o.remaining_balance)}</td>
                                <td style="padding: 12px; text-align: right;"><div style="display: flex; gap: 6px; justify-content: flex-end;">${actions.join('')}</div></td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }
                } catch (e) {
                    console.log('Error loading orders:', e);
                }
            };

            window.copyPaymentLink = async function(orderId) {
                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/orders/' + orderId + '/payment-link', {
                        method: 'POST',
                        headers: headers
                    });
                    if (res.ok) {
                        var data = await res.json();
                        if (data.payment_link) {
                            await navigator.clipboard.writeText(data.payment_link);
                            showOrderAlert('Payment link copied to clipboard for Order #' + orderId, true);
                        }
                    } else {
                        var err = await res.json();
                        showOrderAlert(err.error || 'Failed to generate payment link.', false);
                    }
                } catch (e) {
                    showOrderAlert('Failed to generate payment link: ' + e.message, false);
                }
            };

            window.openRefundModalDecoded = function(encodedOrder) {
                try {
                    var order = JSON.parse(decodeURIComponent(encodedOrder));
                    window.openRefundModal(order);
                } catch (e) {
                    console.error('Failed to parse order for refund modal:', e);
                }
            };

            window.openRefundModal = function(order) {
                window._activeRefundOrder = order;
                var refundableCents = Math.max(0, (order.paid_amount || 0) - (order.total_refunded || 0));

                document.getElementById('refund-order-id').value = order.id;
                document.getElementById('refund-modal-order-number').innerText = '#' + (order.shopify_order_id || order.id);
                document.getElementById('refund-modal-customer').innerText = order.customer_email || '—';
                document.getElementById('refund-modal-paid').innerText = formatAud(order.paid_amount);
                document.getElementById('refund-modal-refunded').innerText = formatAud(order.total_refunded || 0);
                document.getElementById('refund-modal-max').innerText = formatAud(refundableCents);

                document.getElementById('refund-amount-input').value = (refundableCents / 100).toFixed(2);
                document.getElementById('refund-amount-input').max = (refundableCents / 100).toFixed(2);
                document.getElementById('refund-modal-error').style.display = 'none';

                document.getElementById('refund-modal').style.display = 'flex';
            };

            window.closeRefundModal = function() {
                document.getElementById('refund-modal').style.display = 'none';
                window._activeRefundOrder = null;
            };

            window.setFullRefund = function() {
                if (!window._activeRefundOrder) return;
                var refundableCents = Math.max(0, (window._activeRefundOrder.paid_amount || 0) - (window._activeRefundOrder.total_refunded || 0));
                document.getElementById('refund-amount-input').value = (refundableCents / 100).toFixed(2);
            };

            window.submitRefund = async function() {
                if (!window._activeRefundOrder) return;
                var btn = document.getElementById('btn-submit-refund');
                var errEl = document.getElementById('refund-modal-error');
                errEl.style.display = 'none';

                var dollars = parseFloat(document.getElementById('refund-amount-input').value);
                if (isNaN(dollars) || dollars <= 0) {
                    errEl.innerText = 'Please enter a valid refund amount greater than 0.';
                    errEl.style.display = 'block';
                    return;
                }

                var cents = Math.round(dollars * 100);
                var refundableCents = Math.max(0, (window._activeRefundOrder.paid_amount || 0) - (window._activeRefundOrder.total_refunded || 0));
                if (cents > refundableCents) {
                    errEl.innerText = 'Amount exceeds maximum refundable amount of ' + formatAud(refundableCents);
                    errEl.style.display = 'block';
                    return;
                }

                var reason = document.getElementById('refund-reason-select').value;
                btn.disabled = true;
                btn.innerText = 'Refunding via Stripe...';

                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/orders/' + window._activeRefundOrder.id + '/refund', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({
                            amount: cents,
                            reason: reason
                        })
                    });

                    var result = await res.json();
                    if (res.ok) {
                        window.closeRefundModal();
                        showOrderAlert('Refund of ' + formatAud(cents) + ' processed successfully via Stripe.', true);
                        await window.loadOrders();
                        await window.loadDashboard();
                        await window.loadAuditLogs();
                    } else {
                        errEl.innerText = result.error || 'Failed to process refund.';
                        errEl.style.display = 'block';
                    }
                } catch (e) {
                    errEl.innerText = 'Network error communicating with server: ' + e.message;
                    errEl.style.display = 'block';
                } finally {
                    btn.disabled = false;
                    btn.innerText = 'Process Refund';
                }
            };

            window.loadAuditLogs = async function() {
                try {
                    var headers = await getAuthHeaders();
                    var res = await fetch('/api/audit-logs', { headers: headers });
                    if (res.ok) {
                        var json = await res.json();
                        var logs = json.data || json || [];
                        var tbody = document.getElementById('audit-tbody');
                        var emptyState = document.getElementById('audit-empty-state');
                        var table = document.getElementById('audit-table');

                        if (!tbody || !emptyState || !table) return;

                        if (!logs || logs.length === 0) {
                            tbody.innerHTML = '';
                            table.style.display = 'none';
                            emptyState.style.display = 'block';
                            return;
                        }

                        table.style.display = 'table';
                        emptyState.style.display = 'none';
                        tbody.innerHTML = '';

                        logs.forEach(function(l) {
                            var tr = document.createElement('tr');
                            tr.style.borderBottom = '1px solid var(--p-color-border)';

                            var detailsStr = '';
                            if (l.details) {
                                try {
                                    detailsStr = typeof l.details === 'object' ? JSON.stringify(l.details) : l.details;
                                } catch(e) { detailsStr = String(l.details); }
                            }

                            tr.innerHTML = `
                                <td style="padding: 10px 12px; font-family: monospace; font-size: 12px; color: var(--p-color-text-subdued);">${l.created_at || '—'}</td>
                                <td style="padding: 10px 12px;"><strong>${l.action}</strong></td>
                                <td style="padding: 10px 12px;"><span class="badge badge-info">${l.resource_type || ''} #${l.resource_id || ''}</span></td>
                                <td style="padding: 10px 12px; font-size: 13px;">${l.user ? l.user.name : (l.ip_address || 'System')}</td>
                                <td style="padding: 10px 12px; font-family: monospace; font-size: 12px; color: var(--p-color-text-subdued); max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${detailsStr}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }
                } catch (e) {
                    console.log('Error loading audit logs:', e);
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                var initialHash = window.location.hash.replace('#', '');
                if (initialHash && ['overview', 'plans', 'orders', 'audit', 'settings'].includes(initialHash)) {
                    window.navigateTab(initialHash);
                }
                window.refreshOverview();
                window.loadStripeStatus();
                window.loadPlans();
                window.loadOrders();
                window.loadAuditLogs();
            });
        })();
    </script>
</body>
</html>
