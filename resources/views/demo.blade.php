<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Northwind Ceramics — Studio Lab &amp; Testing Cockpit</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/northwind.css">
</head>
<body>
    <div class="demo-page page-enter">
        <header class="demo-topline">
            <a href="{{ $adminUrl ?? '/' }}" class="demo-brand">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                    <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                </svg>
                NORTHWIND <span>STUDIO LAB</span>
            </a>
            <span class="demo-readonly">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
                </svg>
                LOCAL DEV STACK · VERIFIED
            </span>
        </header>

        <main id="main-content" class="demo-main">
            <div class="demo-hero">
                <div>
                    <p class="eyebrow">FLOW INSPECTION COCKPIT · AUTUMN 2026</p>
                    <h1>Every step,<br>from the wheel to the door.</h1>
                    <p>Walk through the Northwind payment experience across all six operational surfaces as a maker or a collector.</p>
                </div>
                <div class="demo-orbit" aria-hidden="true">
                    <span class="orbit-center">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                            <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                        </svg>
                    </span>
                    <i></i><b></b><em></em><small>08</small>
                </div>
            </div>

            <!-- Health Strip -->
            <div class="health-strip">
                <div>
                    <span class="health-pulse"></span>
                    <span>
                        <strong>Frontend architecture</strong>
                        <small>Active · Artisan pottery design system</small>
                    </span>
                </div>
                <div>
                    <span class="health-pulse health-sage"></span>
                    <span>
                        <strong>Local database</strong>
                        <small>{{ $stats['orders'] ?? 12 }} orders · {{ $stats['plans'] ?? 2 }} plans · {{ $stats['customers'] ?? 4 }} customers</small>
                    </span>
                </div>
                <div>
                    <span class="health-pulse health-ochre"></span>
                    <span>
                        <strong>Payment gateway</strong>
                        <small>Stripe SAQ-A scope · encrypted tokens</small>
                    </span>
                </div>
                <div class="health-readonly">READY FOR VERIFICATION</div>
            </div>

            <div class="demo-content-grid">
                <!-- 4 Operational Flows -->
                <section class="demo-flows">
                    <div class="demo-section-head">
                        <div>
                            <p class="eyebrow">THE OPERATIONAL JOURNEY</p>
                            <h2>Choose a place to begin.</h2>
                        </div>
                        <span>04 CORE SURFACES</span>
                    </div>

                    <div class="flow-list">
                        <a class="flow-card" href="{{ $adminUrl ?? '/' }}">
                            <span class="flow-number">01</span>
                            <span class="flow-icon">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="15" y="4" width="5" height="7" rx="1.5"/><rect x="4" y="15" width="7" height="5" rx="1.5"/><rect x="15" y="15" width="5" height="5" rx="1.5"/>
                                </svg>
                            </span>
                            <span class="flow-copy">
                                <small>STUDIO ADMIN</small>
                                <strong>Merchant admin shell</strong>
                                <span>Plans, orders ledger, Ceramic Club, audit trail, settings.</span>
                            </span>
                            <span class="flow-arrow">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>

                        <a class="flow-card" href="/storefront">
                            <span class="flow-number">02</span>
                            <span class="flow-icon">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 8h14l1 12H4L5 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/>
                                </svg>
                            </span>
                            <span class="flow-copy">
                                <small>STOREFRONT</small>
                                <strong>Storefront deposit widget</strong>
                                <span>Made-to-order vase, deposit calculator, checkout interception.</span>
                            </span>
                            <span class="flow-arrow">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>

                        <a class="flow-card" href="{{ $signedPortalUrl ?? '/portal/demo' }}">
                            <span class="flow-number">03</span>
                            <span class="flow-icon">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="9" cy="8" r="3"/><path d="M3.5 19c.4-3 2.3-4.5 5.5-4.5s5.1 1.5 5.5 4.5M16 5.5a3 3 0 0 1 0 5.5M17 14.5c2.2.4 3.5 1.8 4 4"/>
                                </svg>
                            </span>
                            <span class="flow-copy">
                                <small>CUSTOMER PORTAL</small>
                                <strong>Self-service customer account</strong>
                                <span>Ceramic Club management, pause/skip/cancel, balance links.</span>
                            </span>
                            <span class="flow-arrow">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>

                        <a class="flow-card" href="{{ $signedBalanceUrl ?? '/pay/balance/demo' }}">
                            <span class="flow-number">04</span>
                            <span class="flow-icon">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 3.5h11.5L21 7v13.5l-3-1.8-3 1.8-3-1.8-3 1.8-3-1.8z"/><path d="M9 10h6M9 14h7"/>
                                </svg>
                            </span>
                            <span class="flow-copy">
                                <small>HOSTED RECOVERY</small>
                                <strong>Balance settlement &amp; card update</strong>
                                <span>5-step Kiln Stepper, invoice breakdown, SetupIntent recovery.</span>
                            </span>
                            <span class="flow-arrow">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>
                    </div>
                </section>

                <!-- Interactive PDP Simulator -->
                <section class="demo-simulator">
                    <div class="simulator-heading">
                        <div>
                            <p class="eyebrow">LIVE PDP SIMULATOR</p>
                            <h2>A piece in the making.</h2>
                        </div>
                        <span class="sim-dot"></span>
                    </div>

                    <div class="sim-product">
                        <div class="sim-vase-art" aria-hidden="true">
                            <div class="vase-shape"><i></i></div>
                            <span></span>
                        </div>
                        <div>
                            <small>SPECKLED STONEWARE · #08</small>
                            <strong>Everyday stoneware vase</strong>
                            <span>$150.00 AUD · 3–6 weeks</span>
                        </div>
                    </div>

                    <div class="sim-options">
                        <label class="sim-choice sim-choice-active" id="label-choice-deposit">
                            <input name="demo-choice" type="radio" value="deposit" checked onchange="updateSimChoice('deposit')">
                            <span class="choice-radio"></span>
                            <span>
                                <strong>30% pre-order deposit</strong>
                                <small>Balance due when ready to ship</small>
                            </span>
                            <b>$45.00 AUD</b>
                        </label>
                        <label class="sim-choice" id="label-choice-full">
                            <input name="demo-choice" type="radio" value="full" onchange="updateSimChoice('full')">
                            <span class="choice-radio"></span>
                            <span>
                                <strong>Pay in full</strong>
                                <small>One payment today</small>
                            </span>
                            <b>$150.00 AUD</b>
                        </label>
                    </div>

                    <div class="sim-totals">
                        <div>
                            <span>Due today</span>
                            <strong id="sim-due-today">$45.00 AUD</strong>
                        </div>
                        <div>
                            <span>Before dispatch</span>
                            <strong id="sim-due-balance">$105.00 AUD</strong>
                        </div>
                    </div>

                    <a href="/storefront" class="button button-primary button-full">
                        Open interactive storefront
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                    </a>
                    <p class="sim-note">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>
                        </svg>
                        Simulated PDP with deposit interception logic.
                    </p>
                </section>
            </div>

            <!-- Reference Terminal Panel -->
            <section class="terminal-panel">
                <div class="terminal-head">
                    <div>
                        <span class="terminal-dots"><i></i><i></i><i></i></span>
                        <strong>STUDIO TOOLKIT</strong>
                    </div>
                    <span>DEVELOPER REFERENCE</span>
                </div>
                <div class="terminal-grid">
                    <div>
                        <small>STRIPE WEBHOOK LISTENER</small>
                        <code>stripe listen --forward-to localhost:8000/webhooks/stripe</code>
                        <button type="button" onclick="navigator.clipboard.writeText('stripe listen --forward-to localhost:8000/webhooks/stripe'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 1500);">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="8" y="8" width="11" height="12" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h2"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                    <div>
                        <small>FEATURE TEST SUITE</small>
                        <code>php artisan test --testsuite=Feature</code>
                        <button type="button" onclick="navigator.clipboard.writeText('php artisan test --testsuite=Feature'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 1500);">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="8" y="8" width="11" height="12" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h2"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                    <div>
                        <small>DOCKER DEVELOPMENT STACK</small>
                        <code>powershell -ExecutionPolicy Bypass -File scripts/stack.ps1 up</code>
                        <button type="button" onclick="navigator.clipboard.writeText('powershell -ExecutionPolicy Bypass -File scripts/stack.ps1 up'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 1500);">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="8" y="8" width="11" height="12" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h2"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                </div>
                <p class="terminal-disclaimer">Production-ready Laravel 11 &amp; Shopify App Bridge v4 architecture.</p>
            </section>

            <div class="demo-footer">
                <span>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                        <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                    </svg>
                    Northwind Ceramics
                </span>
                <span>Thoughtfully made. Clearly paid.</span>
                <a href="{{ $adminUrl ?? '/' }}">
                    Back to studio admin
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </main>
    </div>

    <script>
    function updateSimChoice(choice) {
        const depositLabel = document.getElementById('label-choice-deposit');
        const fullLabel = document.getElementById('label-choice-full');
        const dueToday = document.getElementById('sim-due-today');
        const dueBalance = document.getElementById('sim-due-balance');

        if (choice === 'deposit') {
            depositLabel.classList.add('sim-choice-active');
            fullLabel.classList.remove('sim-choice-active');
            dueToday.innerText = '$45.00 AUD';
            dueBalance.innerText = '$105.00 AUD';
        } else {
            fullLabel.classList.add('sim-choice-active');
            depositLabel.classList.remove('sim-choice-active');
            dueToday.innerText = '$150.00 AUD';
            dueBalance.innerText = '$0.00 AUD';
        }
    }
    </script>
</body>
</html>
