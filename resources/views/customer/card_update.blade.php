<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Payment Method — Northwind Ceramics</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/northwind.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="hosted-page page-enter">
        <header class="hosted-header">
            <a href="/" class="hosted-brand">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                    <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                </svg>
                <span>NORTHWIND <small>CERAMICS · STUDIO MELBOURNE</small></span>
            </a>
            <span class="hosted-verified">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
                </svg>
                CARD UPDATE · SECURE
            </span>
        </header>

        <main id="main-content" class="hosted-main card-update-main">
            <div class="hosted-overline">
                <span>INSTALLMENT #{{ sprintf('%02d', $schedule->schedule_sequence ?? 2) }} OF {{ sprintf('%02d', $schedule->order->installments_count ?? 3) }}</span>
                <span class="hosted-dot">·</span>
                <span>ACTION NEEDED</span>
            </div>

            <h1>A quick note<br>about your card.</h1>
            <p class="hosted-lead">Update Your Payment Card for your upcoming stoneware installment.</p>

            <section class="card-update-card">
                <div class="card-update-context">
                    <div>
                        <p class="eyebrow">UPCOMING INSTALLMENT</p>
                        <h2>${{ number_format($schedule->amount / 100, 2) }} {{ $order->currency }}</h2>
                        <p>Order #{{ $order->shopify_order_number ?? $order->id }} · {{ $order->line_items_summary ?? 'Handmade ceramic collection' }}</p>
                    </div>
                    <span class="installment-indicator">
                        {{ sprintf('%02d', $schedule->schedule_sequence ?? 2) }}
                        <small>OF {{ sprintf('%02d', $schedule->order->installments_count ?? 3) }}</small>
                    </span>
                </div>

                <div id="payment-form">
                    <div class="card-element-label">
                        <span>PAYMENT METHOD</span>
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
                            </svg>
                            PCI SAQ-A ENCRYPTED
                        </span>
                    </div>

                    <!-- Skeleton while Stripe mounts -->
                    <div id="card-skeleton" class="skeleton-input" style="height: 48px; border-radius: 7px; background: linear-gradient(90deg, #f5efe8 0%, #ffffff 50%, #f5efe8 100%); background-size: 200% 100%; border: 1px solid #e3d9d1; animation: shimmer 1.5s infinite;"></div>

                    <div id="card-element" style="display: none; padding: 13px 14px; border: 1px solid #e3d9d1; border-radius: 7px; background: #ffffff;"></div>
                    <div id="card-errors" role="alert" style="color: var(--red); font-size: 12px; margin-top: 10px; min-height: 20px;"></div>

                    <button id="submit-btn" class="button button-primary button-full card-update-cta" disabled style="margin-top: 16px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
                        </svg>
                        Save &amp; Verify Card
                    </button>
                    <p class="card-card-foot">Automated retry cadence: days 1, 3 &amp; 5 <span>·</span> Zero card numbers stored on studio servers</p>
                </div>

                <div id="success-box" class="settlement-state-card settlement-success card-success-card" style="display: none; margin-top: 14px;">
                    <div class="state-card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    </div>
                    <div>
                        <p class="eyebrow">PAYMENT METHOD UPDATED</p>
                        <h2>Card updated successfully!</h2>
                        <p>Your upcoming installment will be processed automatically with this card. Thank you for keeping the studio in step!</p>
                    </div>
                </div>
            </section>

            <div class="hosted-security">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>
                </svg>
                <span>Direct tokenization via Stripe SetupIntents. PCI DSS SAQ-A compliant.<br><small>Crafted slowly in Melbourne &amp; Byron Bay, Australia.</small></span>
                <span class="stripe-word">stripe</span>
            </div>

            <div class="hosted-footer-links">
                <a href="mailto:emma@northwindceramics.com.au">
                    Need help? Contact the studio
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </a>
                <a href="/portal/demo">Your Northwind account</a>
            </div>
        </main>

        <footer class="hosted-footer">
            NORTHWIND CERAMICS <i></i> HANDMADE IN AUSTRALIA · 2026
        </footer>
    </div>

    <script>
        const stripe = Stripe("{{ $publishableKey }}");
        const elements = stripe.elements();
        const cardElement = elements.create("card", {
            style: {
                base: {
                    fontFamily: '"Inter", -apple-system, sans-serif',
                    fontSize: "14px",
                    color: "#1C1917",
                    "::placeholder": { color: "#82766d" }
                },
                invalid: {
                    color: "#9b2c22"
                }
            }
        });

        const formBtn = document.getElementById("submit-btn");
        const errorEl = document.getElementById("card-errors");
        const successBox = document.getElementById("success-box");
        const paymentForm = document.getElementById("payment-form");
        const cardEl = document.getElementById("card-element");
        const skeleton = document.getElementById("card-skeleton");

        cardElement.on('ready', () => {
            skeleton.style.display = 'none';
            cardEl.style.display = 'block';
            formBtn.disabled = false;
        });

        cardElement.mount("#card-element");

        formBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            formBtn.disabled = true;
            formBtn.textContent = "Verifying with Bank...";
            errorEl.textContent = "";

            const { setupIntent, error } = await stripe.confirmCardSetup(
                "{{ $clientSecret }}",
                { payment_method: { card: cardElement } }
            );

            if (error) {
                errorEl.textContent = error.message;
                formBtn.disabled = false;
                formBtn.textContent = "Save & Verify Card";
            } else {
                const updateUrl = window.location.href;
                const res = await fetch(updateUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ payment_method_id: setupIntent.payment_method })
                });

                if (res.ok) {
                    paymentForm.style.display = "none";
                    successBox.style.display = "flex";
                } else {
                    errorEl.textContent = "Failed to record updated payment method. Please refresh and try again.";
                    formBtn.disabled = false;
                    formBtn.textContent = "Save & Verify Card";
                }
            }
        });
    </script>
</body>
</html>
