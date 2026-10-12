<!-- TAB: OVERVIEW -->
<div id="tab-overview" class="tab-content active" role="tabpanel" aria-labelledby="tab-btn-overview">
    <div class="section-heading">
        <div>
            <p class="eyebrow">STUDIO FINANCIALS</p>
            <h1>The studio at a glance</h1>
            <p class="section-copy">A clear view of what’s been collected, what’s in the kiln, and what comes next.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="button button-secondary" type="button" onclick="window.refreshOverview()" title="Sync shop data from API">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 7v5h-5M5 17v-5h5"/><path d="M6.5 9a6.5 6.5 0 0 1 11-2L19 8M5 16l1.5 1a6.5 6.5 0 0 0 11-2"/>
                </svg>
                <span>Refresh</span>
            </button>
            <button class="button button-primary" type="button" onclick="window.openPlanModal()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                <span>Create Plan</span>
            </button>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="metric-grid">
        <article class="metric-card metric-sage">
            <div class="metric-top">
                <span>Captured Cash</span>
                <span class="metric-icon">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                </span>
            </div>
            <strong id="kpi-collected" class="metric-value">$0.00</strong>
            <div class="metric-foot">
                <span>Settled deposits &amp; full balances</span>
                <small>Settled</small>
            </div>
        </article>

        <article class="metric-card metric-ochre">
            <div class="metric-top">
                <span>Pending Balances</span>
                <span class="metric-icon">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
            </div>
            <strong id="kpi-balance-due" class="metric-value">$0.00</strong>
            <div class="metric-foot">
                <span>Due before kiln dispatch</span>
                <small>Pending</small>
            </div>
        </article>

        <article class="metric-card metric-terracotta">
            <div class="metric-top">
                <span>Upcoming Charges</span>
                <span class="metric-icon">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h11.5L21 7v13.5l-3-1.8-3 1.8-3-1.8-3 1.8-3-1.8z"/><path d="M9 10h6M9 14h7"/></svg>
                </span>
            </div>
            <strong id="kpi-upcoming" class="metric-value">$0.00</strong>
            <div class="metric-foot">
                <span>Due in the next 14 days</span>
                <small>Scheduled</small>
            </div>
        </article>

        <article class="metric-card metric-red">
            <div class="metric-top">
                <span>Failed / Attention</span>
                <span class="metric-icon">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/></svg>
                </span>
            </div>
            <strong id="kpi-failed" class="metric-value">$0.00</strong>
            <div class="metric-foot">
                <span>Off-session card declines</span>
                <small>Requires review</small>
            </div>
        </article>
    </div>

    <!-- Studio Launch Checklist Card -->
    <section class="readiness-card">
        <div class="readiness-copy">
            <div class="readiness-emblem">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/><path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                </svg>
            </div>
            <div>
                <div class="eyebrow">STUDIO SETUP</div>
                <h2>The wheel is turning.</h2>
                <p>Complete these steps to activate deposit pre-orders on your handmade stoneware catalog.</p>
            </div>
        </div>

        <div class="readiness-status">
            <span id="chk-overall-status" class="status-pill status-ochre">Action Required</span>
            <span>3 of 4 steps complete</span>
        </div>

        <div class="setup-steps">
            <div class="setup-step step-complete">
                <span class="step-check" id="chk-shopify-icon">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                </span>
                <div>
                    <small>STEP 01</small>
                    <strong>Install embedded app</strong>
                    <span>Shopify OAuth &amp; App Bridge v4</span>
                </div>
            </div>

            <div class="setup-step step-pending">
                <span class="step-check" id="chk-stripe-icon">02</span>
                <div>
                    <small>STEP 02</small>
                    <strong>Connect Stripe gateway</strong>
                    <span>Finish setup in settings</span>
                </div>
                <a href="#settings" onclick="window.navigateTab('settings'); return false;" aria-label="Configure gateway">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="setup-step step-complete">
                <span class="step-check" id="chk-plan-icon">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                </span>
                <div>
                    <small>STEP 03</small>
                    <strong>Configure deposit plan</strong>
                    <span>30% deposit · active plan</span>
                </div>
                <a href="#plans" onclick="window.navigateTab('plans'); return false;" aria-label="View plans">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="setup-step step-complete">
                <span class="step-check">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                </span>
                <div>
                    <small>STEP 04</small>
                    <strong>Theme app extension</strong>
                    <span>Active via App Proxy</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Lower Overview Grid -->
    <div class="overview-lower">
        <section class="panel architecture-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">PAYMENT ARCHITECTURE</p>
                    <h2>Thoughtful by design.</h2>
                </div>
                <span class="architecture-mark">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/></svg>
                </span>
            </div>
            <p class="panel-description">Stripe-first checkout keeps sensitive card details outside the studio while ensuring integer cents precision across all deposits and off-session balances.</p>
            <div class="architecture-points">
                <div>
                    <span>01</span>
                    <strong>PCI SAQ-A</strong>
                    <small>Card data stays with Stripe</small>
                </div>
                <div>
                    <span>02</span>
                    <strong>Integer cents</strong>
                    <small>Zero rounding surprises</small>
                </div>
                <div>
                    <span>03</span>
                    <strong>Gentle retries</strong>
                    <small>Days 1 · 3 · 5 schedule</small>
                </div>
            </div>
            <div class="arch-footer">
                OPTION B APP PROXY <span>·</span> PRODUCTION ISOLATED <span class="arch-live">● ACTIVE</span>
            </div>
        </section>

        <section class="panel cashflow-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">STUDIO CASH FLOW</p>
                    <h2>Collected, week by week.</h2>
                </div>
                <span class="select-chip">
                    Last 6 weeks
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                </span>
            </div>
            <div class="chart-total">
                <strong>$18,420 AUD</strong>
                <span class="trend-up">↗ 12.8%</span>
            </div>
            <div class="bar-chart" role="img" aria-label="Six-week payment collection trend">
                <div class="chart-y">
                    <span>$8k</span>
                    <span>$4k</span>
                    <span>$0</span>
                </div>
                <div class="bar-wrap"><i style="--bar: 39%; --bar-delay: 0ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 54%; --bar-delay: 35ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 45%; --bar-delay: 70ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 68%; --bar-delay: 105ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 61%; --bar-delay: 140ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 83%; --bar-delay: 175ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 74%; --bar-delay: 210ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 95%; --bar-delay: 245ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 68%; --bar-delay: 280ms;" class="chart-bar"></i></div>
                <div class="bar-wrap"><i style="--bar: 82%; --bar-delay: 315ms;" class="chart-bar chart-bar-current"></i></div>
                <div class="bar-wrap"><i style="--bar: 100%; --bar-delay: 350ms;" class="chart-bar chart-bar-current"></i></div>
                <div class="bar-wrap"><i style="--bar: 88%; --bar-delay: 385ms;" class="chart-bar chart-bar-current"></i></div>
            </div>
            <div class="chart-labels">
                <span>01 Sep</span>
                <span>15 Sep</span>
                <span>29 Sep</span>
                <span>12 Oct</span>
            </div>
        </section>
    </div>

    <!-- Bottom Overview Grid -->
    <div class="overview-bottom">
        <section class="panel quick-actions">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">SHORTCUTS</p>
                    <h2>Pick up where you left off.</h2>
                </div>
            </div>
            <div class="quick-action-list">
                <button type="button" class="quick-action" onclick="window.openPlanModal()">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    <span>
                        <strong>Create a payment plan</strong>
                        <small>Set a deposit or installment schedule</small>
                    </span>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </button>

                <button type="button" class="quick-action" onclick="window.navigateTab('orders')">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h11.5L21 7v13.5l-3-1.8-3 1.8-3-1.8-3 1.8-3-1.8z"/><path d="M9 10h6M9 14h7"/></svg>
                    <span>
                        <strong>Open the orders ledger</strong>
                        <small>Find a piece and inspect its schedule</small>
                    </span>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </button>

                <button type="button" class="quick-action" onclick="window.navigateTab('settings')">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.4-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.4-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a7 7 0 0 1-.1 1.7Z" transform="translate(-1.8 -1.5) scale(1.15)"/></svg>
                    <span>
                        <strong>Stripe gateway settings</strong>
                        <small>Studio credentials &amp; customer emails</small>
                    </span>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </button>

                <button type="button" class="quick-action" onclick="window.exportTransactionsCsv()">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/></svg>
                    <span>
                        <strong>Export transactions</strong>
                        <small>Download bookkeeping CSV ledger</small>
                    </span>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </button>
            </div>
        </section>

        <section class="panel profile-panel">
            <div class="profile-top">
                <span class="profile-avatar">EC</span>
                <div>
                    <p class="eyebrow">YOUR STUDIO</p>
                    <h2>Northwind Ceramics</h2>
                    <span>Emma Carter · Studio Owner</span>
                </div>
            </div>
            <div class="profile-details">
                <div>
                    <span>Studio locations</span>
                    <strong>Melbourne &amp; Byron Bay</strong>
                </div>
                <div>
                    <span>Connected shop</span>
                    <strong>{{ $shop ?? 'northwind-ceramics.myshopify.com' }}</strong>
                </div>
                <div>
                    <span>Gateway status</span>
                    <strong class="gateway-line">
                        <i class="signal-dot"></i>
                        <span id="overview-gateway-status-text">Stripe Connected</span>
                    </strong>
                </div>
            </div>
            <a href="#settings" onclick="window.navigateTab('settings'); return false;" class="text-link">
                Studio settings
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
            </a>
        </section>
    </div>
</div>
