<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speckled Stoneware Vase — Northwind Ceramics</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/northwind.css">
</head>
<body>
    <div class="storefront-page page-enter">
        <div class="shop-announcement">
            <span>
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                    <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                </svg>
                MADE SLOWLY IN MELBOURNE &amp; BYRON BAY
            </span>
            <span>Complimentary shipping across Australia on orders over $250 AUD</span>
        </div>

        <nav class="storefront-crumb" aria-label="Breadcrumb">
            <a href="/">Northwind Ceramics</a>
            <span>/</span>
            <a href="/storefront">The collection</a>
            <span>/</span>
            <span>Speckled stoneware</span>
        </nav>

        <div class="product-layout">
            <!-- Product Gallery Column -->
            <section class="product-gallery">
                <div class="product-image-wrap">
                    <div style="width: 100%; height: 420px; background: linear-gradient(135deg, #f5efe8 0%, #ebe0d5 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 10px; position: relative;">
                        <div class="sim-vase-art" aria-hidden="true" style="transform: scale(1.6); margin-bottom: 20px;">
                            <div class="vase-shape"><i></i></div>
                            <span></span>
                        </div>
                        <span style="font-family: var(--serif); font-size: 15px; color: var(--terracotta); font-style: italic;">No. 08 · Autumn Firing</span>
                    </div>
                    <span class="image-index">01 <span>/</span> 01</span>
                    <span class="image-studio-tag">HAND-THROWN · SMALL BATCH</span>
                </div>

                <div class="gallery-caption">
                    <span>Autumn firing · 2026</span>
                    <span class="gallery-counter"><i class="active"></i></span>
                    <span>Made to order, just for you.</span>
                </div>

                <div class="product-note-card">
                    <span class="note-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/>
                        </svg>
                    </span>
                    <p>Each piece is shaped by hand on the wheel. Fine natural variations in form, speckle, and glaze finish are part of what makes yours, yours.</p>
                </div>
            </section>

            <!-- Product Details & Deposit Checkout Column -->
            <section class="product-detail">
                <div class="collection-label">
                    <span class="collection-line"></span> THE STUDIO COLLECTION · NO. 08
                </div>
                <h1>Speckled<br>stoneware vase</h1>
                <div class="product-byline">Wheel-thrown in our Byron Bay studio <span>·</span> 24 cm height</div>
                
                <div class="product-rating">
                    <span style="color: var(--terracotta); letter-spacing: 2px;">★★★★★</span>
                    <strong>4.9</strong>
                    <small>from 28 collectors</small>
                </div>

                <div class="product-price-row">
                    <strong>$200.00 AUD</strong>
                    <span>Taxes included · AUD</span>
                </div>

                <p class="product-description">
                    A generous, sculptural vessel with an easy shape and a soft, speckled chalk glaze. Made slowly on the wheel, trimmed by hand, and fired over 48 hours in our studio kiln.
                </p>

                <div class="lead-time-badge">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                        <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                    </svg>
                    <span>
                        <strong>Handmade: 3–6 weeks kiln lead time</strong>
                        <small>Your piece takes the time it needs.</small>
                    </span>
                </div>

                <!-- Northwind Deposit Plan Selection Widget -->
                <form class="payment-widget" id="storefront-checkout-form" onsubmit="event.preventDefault(); openStorefrontCheckout();">
                    <div class="widget-header">
                        <div>
                            <p class="eyebrow">A PAYMENT THAT FITS THE MAKING</p>
                            <h2>Choose how to pay</h2>
                        </div>
                        <span class="widget-lock">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>
                            </svg>
                            PREVIEW
                        </span>
                    </div>

                    <div class="payment-choices">
                        <label class="choice-card" id="card-plan-full">
                            <input type="radio" name="checkout-plan" value="full" onchange="setStoreChoice('full')">
                            <span class="choice-radio"></span>
                            <span class="choice-main">
                                <strong>Pay in full</strong>
                                <small>One simple payment today</small>
                            </span>
                            <span class="choice-amount">$200.00 AUD</span>
                        </label>

                        <label class="choice-card choice-card-selected" id="card-plan-deposit">
                            <input type="radio" name="checkout-plan" value="deposit" checked onchange="setStoreChoice('deposit')">
                            <span class="choice-radio"></span>
                            <span class="choice-main">
                                <strong>Pay a 30% deposit</strong>
                                <small>Balance of $140.00 AUD before dispatch</small>
                            </span>
                            <span class="choice-amount">$60.00 AUD today</span>
                        </label>

                        <label class="choice-card" id="card-plan-installment">
                            <input type="radio" name="checkout-plan" value="installment" onchange="setStoreChoice('installment')">
                            <span class="choice-radio"></span>
                            <span class="choice-main">
                                <strong>Split into 3 payments</strong>
                                <small>Two more payments, every 2 weeks</small>
                            </span>
                            <span class="choice-amount">$66.67 AUD today</span>
                        </label>
                    </div>

                    <div class="schedule-breakdown">
                        <div class="breakdown-heading">
                            <span>YOUR PAYMENT SCHEDULE</span>
                            <span>ALL PRICES AUD</span>
                        </div>
                        
                        <div id="breakdown-deposit-view">
                            <div class="breakdown-row">
                                <span>
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="color: var(--sage); display: inline-block; vertical-align: -2px;"><path d="m5 12 4 4L19 6"/></svg>
                                    Due today · 30% deposit
                                </span>
                                <strong>$60.00 AUD</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="color: var(--ochre); display: inline-block; vertical-align: -2px;"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                    Before dispatch · remaining balance
                                </span>
                                <strong>$140.00 AUD</strong>
                            </div>
                            <div class="breakdown-total">
                                <span>Order total</span>
                                <strong>$200.00 AUD</strong>
                            </div>
                        </div>

                        <div id="breakdown-full-view" style="display: none;">
                            <div class="breakdown-row">
                                <span>Pay today in full</span>
                                <strong>$200.00 AUD</strong>
                            </div>
                            <div class="breakdown-row breakdown-muted">
                                <span>Balance due later</span>
                                <strong>$0.00 AUD</strong>
                            </div>
                            <div class="breakdown-total">
                                <span>Order total</span>
                                <strong>$200.00 AUD</strong>
                            </div>
                        </div>

                        <div id="breakdown-installment-view" style="display: none;">
                            <div class="installment-list">
                                <div>
                                    <span class="inst-number inst-done">1</span>
                                    <span><strong>Today</strong><small>Reserve your piece</small></span>
                                    <b>$66.67 AUD</b>
                                </div>
                                <div>
                                    <span class="inst-number">2</span>
                                    <span><strong>In 2 weeks</strong><small>While it takes shape</small></span>
                                    <b>$66.67 AUD</b>
                                </div>
                                <div>
                                    <span class="inst-number">3</span>
                                    <span><strong>In 4 weeks</strong><small>Before courier dispatch</small></span>
                                    <b>$66.66 AUD</b>
                                </div>
                            </div>
                            <div class="breakdown-total">
                                <span>3 payments · exact total</span>
                                <strong>$200.00 AUD</strong>
                            </div>
                        </div>
                    </div>

                    <div class="terms-copy">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
                        </svg>
                        <span>App Proxy deposit checkout enabled. Customers receive automated kiln updates and reminder notifications.</span>
                    </div>

                    <button type="submit" class="button button-primary button-full product-cta" id="btn-product-cta">
                        Reserve with $60.00 AUD today
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                    </button>

                    <div class="widget-footnote">
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>
                            </svg>
                            Encrypted checkout preview
                        </span>
                        <span>30-day studio care promise</span>
                    </div>
                </form>

                <div class="product-assurances">
                    <span>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Zm-9 5v9l9 5 9-5V8M12 13v9"/></svg>
                        Thoughtfully packed
                    </span>
                    <span>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/></svg>
                        Made by hand
                    </span>
                    <span>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/></svg>
                        Protected payments
                    </span>
                </div>
            </section>
        </div>

        <section class="product-story">
            <p class="eyebrow">A SHAPE TO KEEP</p>
            <h2>Made for the stems that<br>find their way home.</h2>
            <p>Thrown in a single sitting on the studio wheel, then left to dry slowly before its bisque firing. Small freckles bloom in the glaze where the natural Byron iron speaks through the clay.</p>
            <a href="/portal/demo">
                Already have a piece on the way? Follow its journey
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
            </a>
        </section>

        <footer class="shop-footer">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
            </svg>
            <span>NORTHWIND CERAMICS</span>
            <i></i>
            Handmade in Australia, with time and care.
        </footer>
    </div>

    <!-- Checkout Modal Handoff Dialog -->
    <div id="checkout-modal" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
        <div class="nc-modal-dialog" role="dialog" aria-modal="true" style="max-width: 480px;">
            <div class="nc-modal-header">
                <div>
                    <p class="eyebrow" style="margin-bottom: 3px;">CHECKOUT INTERCEPTION</p>
                    <h3 id="checkout-modal-title">Deposit Plan Selected</h3>
                </div>
                <button type="button" class="nc-btn-close" onclick="closeStorefrontCheckout()" aria-label="Close dialog">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="nc-modal-body">
                <div class="checkout-preview" style="text-align: center; padding: 10px 0;">
                    <span class="checkout-icon" style="display: inline-grid; place-items: center; width: 44px; height: 44px; background: var(--terracotta-wash); border-radius: 12px; color: var(--terracotta); margin-bottom: 12px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>
                        </svg>
                    </span>
                    <h2 id="checkout-charge-amount" style="font-family: var(--serif); font-size: 26px; color: var(--terracotta); margin-bottom: 4px;">$60.00 AUD</h2>
                    <p id="checkout-charge-label" style="font-size: 13px; color: var(--nc-charcoal-600); margin-bottom: 18px;">30% pre-order deposit for Speckled Stoneware Vase</p>

                    <div style="background: var(--clay-50); border: 1px solid var(--line); border-radius: 8px; padding: 14px 16px; text-align: left; font-size: 13px; margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span>Order Total</span>
                            <strong>$200.00 AUD</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: var(--ochre);">
                            <span>Due Before Dispatch</span>
                            <strong id="checkout-balance-amount">$140.00 AUD</strong>
                        </div>
                    </div>

                    <div class="notice-soft" style="display: flex; align-items: flex-start; gap: 8px; padding: 12px; background: var(--clay-50); border: 1px solid var(--line); border-radius: 8px; font-size: 12px; text-align: left; color: var(--nc-charcoal-600); margin-bottom: 18px;">
                        <svg class="icon" style="color: var(--sage); flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/></svg>
                        <span>App Proxy checkout interception creates the draft order with partial deposit lines and automated billing schedules.</span>
                    </div>

                    <div class="dialog-footer">
                        <button type="button" class="button button-secondary" onclick="closeStorefrontCheckout()">Back to piece</button>
                        <a href="/pay/balance/demo" class="button button-primary">
                            Simulate balance settlement
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let activeStoreChoice = 'deposit';

    function setStoreChoice(choice) {
        activeStoreChoice = choice;
        document.getElementById('card-plan-full').classList.toggle('choice-card-selected', choice === 'full');
        document.getElementById('card-plan-deposit').classList.toggle('choice-card-selected', choice === 'deposit');
        document.getElementById('card-plan-installment').classList.toggle('choice-card-selected', choice === 'installment');

        document.getElementById('breakdown-full-view').style.display = choice === 'full' ? 'block' : 'none';
        document.getElementById('breakdown-deposit-view').style.display = choice === 'deposit' ? 'block' : 'none';
        document.getElementById('breakdown-installment-view').style.display = choice === 'installment' ? 'block' : 'none';

        const ctaBtn = document.getElementById('btn-product-cta');
        if (choice === 'full') {
            ctaBtn.innerHTML = 'Continue with full payment · $200.00 AUD <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"/></svg>';
        } else if (choice === 'installment') {
            ctaBtn.innerHTML = 'Start 3 payments · $66.67 AUD today <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"/></svg>';
        } else {
            ctaBtn.innerHTML = 'Reserve with $60.00 AUD today <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"/></svg>';
        }
    }

    function openStorefrontCheckout() {
        const modal = document.getElementById('checkout-modal');
        const chargeEl = document.getElementById('checkout-charge-amount');
        const labelEl = document.getElementById('checkout-charge-label');
        const balEl = document.getElementById('checkout-balance-amount');

        if (activeStoreChoice === 'full') {
            chargeEl.innerText = '$200.00 AUD';
            labelEl.innerText = 'Full payment today for Speckled Stoneware Vase';
            balEl.innerText = '$0.00 AUD';
        } else if (activeStoreChoice === 'installment') {
            chargeEl.innerText = '$66.67 AUD';
            labelEl.innerText = 'First installment (1 of 3) for Speckled Stoneware Vase';
            balEl.innerText = '$133.33 AUD (2 x $66.67)';
        } else {
            chargeEl.innerText = '$60.00 AUD';
            labelEl.innerText = '30% pre-order deposit for Speckled Stoneware Vase';
            balEl.innerText = '$140.00 AUD';
        }

        document.body.style.overflow = 'hidden';
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => modal.classList.add('is-open'));
    }

    function closeStorefrontCheckout() {
        const modal = document.getElementById('checkout-modal');
        document.body.style.overflow = '';
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        setTimeout(() => modal.style.display = 'none', 240);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeStorefrontCheckout();
    });
    </script>
</body>
</html>
