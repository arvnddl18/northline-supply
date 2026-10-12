<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="shopify-api-key" content="{{ $apiKey }}">
    <meta name="shopify-shop-domain" content="{{ $shop }}">
    <meta name="shopify-host" content="{{ $host }}">
    <meta name="shopify-session-token" content="{{ $devSessionToken ?? '' }}">
    <title>Northwind Ceramics — Studio Payments &amp; Pre-Orders</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Shopify App Bridge v4 -->
    <script src="https://cdn.shopify.com/shopify-app-bridge-types/v4/index.js"></script>
    <script src="https://cdn.shopify.com/shopify-app-bridge/latest/index.js" data-api-key="{{ $apiKey }}"></script>

    <link rel="stylesheet" href="/css/northwind.css">
    @include('partials.admin.styles')
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <ui-nav-menu>
        <a href="/" rel="home">Overview</a>
        <a href="#plans">Payment Plans</a>
        <a href="#orders">Orders</a>
        <a href="#subscriptions">Subscriptions</a>
        <a href="#audit">Audit Trail</a>
        <a href="#settings">Settings</a>
    </ui-nav-menu>

    <ui-title-bar title="Northwind Ceramics Payments">
        <button variant="primary" onclick="window.openPlanModal()">Create Plan</button>
    </ui-title-bar>

    <div class="admin-shell">
        @include('partials.admin.nav')

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="breadcrumbs">
                    <span>Northwind Ceramics</span>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m7 10 5 5 5-5"/>
                    </svg>
                    <strong id="active-tab-breadcrumb">Overview</strong>
                </div>
                <div class="topbar-right">
                    <span class="demo-indicator"><i></i> Demo workspace</span>
                    <button type="button" class="icon-button notification-button" aria-label="Notifications" onclick="window.showToast('All studio webhooks and payment systems operating normally', 'info')">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>
                        </svg>
                        <i></i>
                    </button>
                    <label class="role-select-label" for="role-select">VIEW AS</label>
                    <select id="role-select" class="role-select" aria-label="Choose demo role" onchange="window.switchRole ? window.switchRole(this.value) : null">
                        <option value="owner" selected>Owner</option>
                        <option value="staff">Studio staff</option>
                    </select>
                </div>
            </header>

            <div class="surface-switcher" aria-label="Prototype surfaces">
                <a href="#overview" onclick="window.navigateTab('overview'); return false;" class="surface-switch-active" id="surf-link-studio">Studio</a>
                <a href="/storefront" target="_blank">Storefront</a>
                <a href="/portal/demo" target="_blank">Customer portal</a>
                <a href="/pay/balance/demo" target="_blank">Settlement</a>
                <a href="/pay/card/demo" target="_blank">Card update</a>
                <a href="/demo" target="_blank">Studio lab</a>
            </div>

            <main id="main-content" class="admin-content page-enter" tabindex="-1">
                <div class="views-container">
                    @include('partials.admin.views.overview')
                    @include('partials.admin.views.plans')
                    @include('partials.admin.views.orders')
                    @include('partials.admin.views.subscriptions')
                    @include('partials.admin.views.audit')
                    @include('partials.admin.views.settings')
                </div>
            </main>

            <footer class="app-footer">
                <span>Northwind Ceramics · Melbourne &amp; Byron Bay</span>
                <span>Studio payments <span class="footer-sep">·</span> Handcrafted with precision</span>
            </footer>
        </div>
    </div>

    @include('partials.admin.drawers')
    @include('partials.admin.modals')
    @include('partials.admin.components.toast-stack')
    @include('partials.admin.scripts')
</body>
</html>
