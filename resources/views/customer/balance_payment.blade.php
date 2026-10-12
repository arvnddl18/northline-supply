<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Northwind Ceramics — Complete Pre-Order Balance</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/northwind.css">
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
                BALANCE PAYMENT · SECURE
            </span>
        </header>

        <main id="main-content" class="hosted-main">
            <div class="hosted-overline">
                <span>ORDER #{{ $orderNumber ?? 'PRE-ORDER' }}</span>
                <span class="hosted-dot">·</span>
                <span>{{ $isPaid ? 'SETTLEMENT COMPLETE' : (!empty($error) ? 'LINK EXPIRED' : 'MADE TO ORDER') }}</span>
            </div>

            <h1>
                @if($isPaid)
                    The final piece<br>of the journey.
                @elseif(!empty($error))
                    This link has<br>found its end.
                @else
                    Your piece is<br>ready for its last step.
                @endif
            </h1>

            <p class="hosted-lead">
                @if($isPaid)
                    The balance is settled. Your pottery is being wrapped with sustainable studio materials for its way home.
                @elseif(!empty($error))
                    {{ $error }} We’ll be glad to prepare a fresh link for you.
                @else
                    Your handmade stoneware is glazed, rested, and ready to make its way to you.
                @endif
            </p>

            <!-- 5-Step Pottery Kiln Stepper Card -->
            <section class="kiln-stepper-card {{ $isPaid ? 'stepper-card-paid' : (!empty($error) ? 'stepper-card-expired' : '') }}">
                <div class="stepper-heading">
                    <span class="eyebrow">THE KILN JOURNEY</span>
                    <span>{{ $isPaid ? 'ALL SETTLED' : (!empty($error) ? 'LINK NOT ACTIVE' : 'KILN READY') }}</span>
                </div>
                <div class="kiln-stepper">
                    @php
                        $steps = ['30% Deposit', 'Wheel thrown', 'Glazed', 'Final balance', 'Courier dispatch'];
                    @endphp
                    @foreach($steps as $idx => $label)
                        @php
                            $stepDone = $isPaid || $idx < 3;
                            $stepActive = !$isPaid && empty($error) && $idx === 3;
                            $stepMuted = !empty($error) && $idx >= 3;
                        @endphp
                        <div class="kiln-step {{ $stepDone ? 'kiln-step-done' : '' }} {{ $stepActive ? 'kiln-step-active' : '' }} {{ $stepMuted ? 'kiln-step-muted' : '' }}">
                            <span class="kiln-step-icon">
                                @if($stepDone)
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                @elseif($stepActive)
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                @else
                                    {{ sprintf('%02d', $idx + 1) }}
                                @endif
                            </span>
                            <span>{{ $label }}</span>
                        </div>
                        @if($idx < count($steps) - 1)
                            <i class="stepper-connector {{ $stepDone ? 'connector-done' : '' }}"></i>
                        @endif
                    @endforeach
                </div>
            </section>

            @if($isPaid)
                <section class="settlement-state-card settlement-success">
                    <div class="state-card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    </div>
                    <div>
                        <p class="eyebrow">BALANCE SETTLED IN FULL</p>
                        <h2>Your piece is being packed.</h2>
                        <p>Your ceramic piece has been paid in full and is currently being packaged with sustainable studio materials for courier dispatch.</p>
                    </div>
                </section>
            @elseif(!empty($error))
                <section class="settlement-state-card settlement-warning">
                    <div class="state-card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </div>
                    <div>
                        <p class="eyebrow">PAYMENT LINK NOTICE</p>
                        <h2>A fresh link is one note away.</h2>
                        <p>{{ $error }} Please contact Emma Carter at Northwind Ceramics and she’ll send a fresh secure payment link.</p>
                        <a class="button button-secondary" href="mailto:emma@northwindceramics.com.au">
                            Ask Emma for a new link
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </section>
            @else
                <!-- Settlement Invoice Breakdown Card -->
                <section class="settlement-invoice">
                    <div class="invoice-header">
                        <div>
                            <p class="eyebrow">Handmade Pre-order Settlement</p>
                            <h2>Order #{{ $orderNumber }}</h2>
                        </div>
                        <span class="status-pill status-ochre">Kiln ready · due {{ $dueDate }}</span>
                    </div>

                    <div class="invoice-row">
                        <span>Total order value</span>
                        <strong>{{ $currency }} {{ $totalFormatted }}</strong>
                    </div>

                    <div class="invoice-row">
                        <span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="color: var(--sage); display: inline-block; vertical-align: -2px;"><path d="m5 12 4 4L19 6"/></svg>
                            Deposit captured upfront
                        </span>
                        <strong class="text-sage">− {{ $currency }} {{ $depositFormatted }}</strong>
                    </div>

                    <div class="invoice-divider"></div>

                    <div class="invoice-row invoice-balance">
                        <span>Remaining balance due</span>
                        <strong>{{ $currency }} {{ $balanceFormatted }}</strong>
                    </div>

                    <form method="POST" action="{{ $checkoutUrl }}">
                        @csrf
                        <button class="button button-primary button-full settlement-pay" type="submit">
                            Pay Balance Now · {{ $currency }} {{ $balanceFormatted }}
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                        </button>
                    </form>
                    <p class="invoice-note">Direct 256-bit SSL encrypted checkout powered by Stripe.</p>
                </section>
            @endif

            <div class="hosted-security">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>
                </svg>
                <span>PCI SAQ-A compliant checkout. Zero cardholder data stored on studio servers.<br><small>Handmade with care in Melbourne &amp; Byron Bay, Australia.</small></span>
                <span class="stripe-word">stripe</span>
            </div>

            <div class="hosted-footer-links">
                <a href="mailto:emma@northwindceramics.com.au">
                    Questions? Write to the studio
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                </a>
                <a href="/portal/demo">Your Northwind account</a>
            </div>
        </main>

        <footer class="hosted-footer">
            NORTHWIND CERAMICS <i></i> HANDMADE IN AUSTRALIA · 2026
        </footer>
    </div>
</body>
</html>
