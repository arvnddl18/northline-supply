<aside class="sidebar" aria-label="Studio navigation">
    <a class="sidebar-brand" href="#overview" onclick="window.navigateTab('overview'); return false;">
        <svg class="icon sidebar-kiln" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
            <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
        </svg>
        <span>
            <strong>northwind</strong>
            <small>ceramics studio</small>
        </span>
    </a>

    <div class="sidebar-label">WORKSPACE</div>

    <nav class="sidebar-nav" role="tablist" aria-label="Admin Navigation Tabs">
        <button type="button" class="nav-link nav-tab active" role="tab" id="tab-btn-overview" aria-selected="true" aria-controls="tab-overview" onclick="window.navigateTab('overview')">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="15" y="4" width="5" height="7" rx="1.5"/><rect x="4" y="15" width="7" height="5" rx="1.5"/><rect x="15" y="15" width="5" height="5" rx="1.5"/>
            </svg>
            <span>Overview</span>
        </button>

        <button type="button" class="nav-link nav-tab" role="tab" id="tab-btn-plans" aria-selected="false" aria-controls="tab-plans" onclick="window.navigateTab('plans')">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 6.5h14M5 12h14M5 17.5h9"/><circle cx="17.5" cy="17.5" r="2.5"/>
            </svg>
            <span>Payment plans</span>
        </button>

        <button type="button" class="nav-link nav-tab" role="tab" id="tab-btn-orders" aria-selected="false" aria-controls="tab-orders" onclick="window.navigateTab('orders')">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 3.5h11.5L21 7v13.5l-3-1.8-3 1.8-3-1.8-3 1.8-3-1.8z"/><path d="M9 10h6M9 14h7"/>
            </svg>
            <span>Orders ledger</span>
            <span class="nav-count" id="nav-failed-count" style="display: none;">0</span>
        </button>

        <button type="button" class="nav-link nav-tab" role="tab" id="tab-btn-subscriptions" aria-selected="false" aria-controls="tab-subscriptions" onclick="window.navigateTab('subscriptions')">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="8" r="3"/><path d="M3.5 19c.4-3 2.3-4.5 5.5-4.5s5.1 1.5 5.5 4.5M16 5.5a3 3 0 0 1 0 5.5M17 14.5c2.2.4 3.5 1.8 4 4"/>
            </svg>
            <span>Ceramic Club</span>
        </button>

        <button type="button" class="nav-link nav-tab" role="tab" id="tab-btn-audit" aria-selected="false" aria-controls="tab-audit" onclick="window.navigateTab('audit')">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 11a8 8 0 1 1 .6 4.3M4 5v6h6"/><path d="M12 7.5v5l3.5 2"/>
            </svg>
            <span>Studio audit</span>
        </button>

        <button type="button" class="nav-link nav-tab" role="tab" id="tab-btn-settings" aria-selected="false" aria-controls="tab-settings" onclick="window.navigateTab('settings')">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.4-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.4-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a7 7 0 0 1-.1 1.7Z" transform="translate(-1.8 -1.5) scale(1.15)"/>
            </svg>
            <span>Settings</span>
        </button>
    </nav>

    <div class="sidebar-kiln-card">
        <div class="kiln-card-icon">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
            </svg>
        </div>
        <p>IN THE STUDIO</p>
        <strong>Autumn firing · #08</strong>
        <span>6 pieces · dispatch in 8 days</span>
        <div class="mini-progress"><i style="width: 72%;"></i></div>
    </div>

    <div class="sidebar-bottom">
        <span class="avatar avatar-clay">EC</span>
        <div class="user-lines">
            <strong>Emma Carter</strong>
            <small>Studio owner</small>
        </div>
        <button class="icon-button" type="button" aria-label="Profile options" onclick="window.navigateTab('settings')">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m7 10 5 5 5-5"/>
            </svg>
        </button>
    </div>
</aside>
