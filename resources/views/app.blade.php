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
    </style>
</head>
<body>
    <!-- Shopify App Bridge Navigation Sidebar Integration -->
    <ui-nav-menu>
        <a href="/" rel="home">Overview</a>
        <a href="#plans">Payment Plans</a>
        <a href="#orders">Orders</a>
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
            <button class="nav-tab" role="tab" id="tab-btn-settings" onclick="window.navigateTab('settings')">Settings</button>
        </nav>

        <!-- TAB: OVERVIEW -->
        <div id="tab-overview" class="tab-content active">
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
                    <h3>Deposit &amp; Installment Plans</h3>
                    <button class="btn btn-primary" onclick="alert('Plans API & Editor available in S1-T5')">+ New Plan</button>
                </div>
                <p style="color: var(--p-color-text-subdued); margin-bottom: 16px;">Configure deposit percentages or fixed amounts applied to specific products or collections.</p>
                <div style="background: var(--p-color-bg-surface-secondary); padding: 24px; text-align: center; border-radius: 6px;">
                    <p style="font-weight: 500; margin-bottom: 8px;">No plans configured yet</p>
                    <p style="color: var(--p-color-text-subdued); font-size: 13px; margin-bottom: 16px;">Connect Stripe and create your first deposit plan to enable the storefront widget.</p>
                    <button class="btn btn-secondary" onclick="window.navigateTab('settings')">Check Stripe Status</button>
                </div>
            </div>
        </div>

        <!-- TAB: ORDERS -->
        <div id="tab-orders" class="tab-content">
            <div class="polaris-card">
                <div class="polaris-card-header">
                    <h3>Order Ledger &amp; Balance Schedules</h3>
                </div>
                <p style="color: var(--p-color-text-subdued); margin-bottom: 16px;">Track pre-orders, deposit settlements, and automated balance payment links.</p>
                <div style="background: var(--p-color-bg-surface-secondary); padding: 24px; text-align: center; border-radius: 6px;">
                    <p style="font-weight: 500;">Order management unlocks in Sprint 2</p>
                    <p style="color: var(--p-color-text-subdued); font-size: 13px;">Full order status tags, payment schedules, and manual balance payment link triggers will be displayed here.</p>
                </div>
            </div>
        </div>

        <!-- TAB: SETTINGS -->
        <div id="tab-settings" class="tab-content">
            <div class="polaris-card">
                <div class="polaris-card-header">
                    <h3>Stripe Account &amp; Payments Settings</h3>
                    <span class="badge badge-warning" id="settings-stripe-badge">Disconnected</span>
                </div>
                <p style="color: var(--p-color-text-subdued); margin-bottom: 16px;">Manage your Stripe API connection, default settlement currency, and webhook signing secret.</p>
                <div style="display: flex; flex-direction: column; gap: 12px; max-width: 480px;">
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 4px;">Primary Currency</label>
                        <input type="text" value="{{ $currency }}" disabled style="width: 100%; padding: 8px; border: 1px solid var(--p-color-border); border-radius: 4px; background: #f9f9f9;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 500; margin-bottom: 4px;">Stripe Connection (S1-T4)</label>
                        <button class="btn btn-primary" onclick="alert('Stripe Connection flow provisioned in S1-T4')">Connect Stripe Account</button>
                    </div>
                </div>
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

            // Listen to hash changes for deep linking
            window.addEventListener('hashchange', function() {
                var hash = window.location.hash.replace('#', '');
                if (hash && ['overview', 'plans', 'orders', 'settings'].includes(hash)) {
                    window.navigateTab(hash);
                }
            });

            // Initial overview fetch using App Bridge session token
            window.refreshOverview = async function() {
                try {
                    var token = null;
                    if (window.shopify && typeof window.shopify.idToken === 'function') {
                        token = await window.shopify.idToken();
                    }

                    var headers = {
                        'Accept': 'application/json',
                    };
                    if (token) {
                        headers['Authorization'] = 'Bearer ' + token;
                    }

                    var res = await fetch('/api/overview', { headers: headers });
                    if (res.ok) {
                        var data = await res.json();
                        if (data.checklist) {
                            if (data.checklist.stripe_connected) {
                                document.getElementById('chk-stripe-icon').className = 'checklist-icon done';
                                document.getElementById('chk-stripe-icon').innerHTML = '&#10003;';
                                document.getElementById('settings-stripe-badge').className = 'badge badge-success';
                                document.getElementById('settings-stripe-badge').innerText = 'Connected';
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
            };

            document.addEventListener('DOMContentLoaded', function() {
                var initialHash = window.location.hash.replace('#', '');
                if (initialHash && ['overview', 'plans', 'orders', 'settings'].includes(initialHash)) {
                    window.navigateTab(initialHash);
                }
                window.refreshOverview();
            });
        })();
    </script>
</body>
</html>
