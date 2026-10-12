<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Account Portal — Northwind Ceramics</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/northwind.css">
</head>
<body>
    <div class="customer-page page-enter">
        <header class="customer-header">
            <a href="/" class="customer-brand">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                    <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                </svg>
                <span>NORTHWIND <small>CERAMICS</small></span>
            </a>
            <div class="customer-header-right">
                <span class="handmade-pill">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/>
                    </svg>
                    Handmade in Australia
                </span>
                <a href="/portal/expired" class="button button-secondary" style="font-size: 11px; padding: 4px 10px; min-height: 28px;">
                    Sign out
                </a>
            </div>
        </header>

        <main id="main-content" class="portal-main">
            <div class="portal-intro">
                <div>
                    <p class="eyebrow">YOUR NORTHWIND STUDIO ACCOUNT</p>
                    @php
                        $rawName = explode('@', $customer->email)[0];
                        $displayName = ucfirst(preg_replace('/[^a-zA-Z0-9]/', ' ', $rawName));
                        $initials = strtoupper(substr($rawName, 0, 2));
                    @endphp
                    <h1>Welcome, {{ $displayName }}.</h1>
                    <p>{{ $customer->email }} · A little home for your pieces in progress and your Ceramic Club deliveries.</p>
                </div>
                <span class="portal-avatar">{{ $initials }}</span>
            </div>

            @if(session('success'))
                <div style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #edf3ed; border: 1px solid #d7e2d7; border-radius: 8px; color: var(--sage); font-size: 13px; margin-bottom: 24px;">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #fdebea; border: 1px solid #f8b4b4; border-radius: 8px; color: var(--red); font-size: 13px; margin-bottom: 24px;">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="portal-grid">
                <section class="portal-main-col">
                    <!-- Ceramic Club Memberships -->
                    @forelse($subscriptions as $sub)
                        <article class="portal-membership-card">
                            <div class="membership-card-top">
                                <div>
                                    <p class="eyebrow">Ceramic Club Subscriptions</p>
                                    <h2>{{ $sub->plan?->name ?? 'Stoneware Studio Club' }}</h2>
                                </div>
                                @php
                                    $subStatusClass = $sub->isActive() ? 'status-sage' : ($sub->isPaused() ? 'status-ochre' : 'status-clay');
                                @endphp
                                <span class="status-pill {{ $subStatusClass }}">{{ ucfirst($sub->status) }}</span>
                            </div>

                            <div class="membership-details">
                                <div>
                                    <span>Monthly investment</span>
                                    <strong>${{ number_format(($sub->plan?->deposit_value ?? 0) / 100, 2) }} <small>{{ $sub->shop?->currency ?? 'AUD' }} / mo</small></strong>
                                </div>
                                <div>
                                    <span>Next kiln renewal</span>
                                    <strong>{{ $sub->next_renewal_at ? $sub->next_renewal_at->format('d M Y') : 'Scheduled' }}</strong>
                                </div>
                                <div>
                                    <span>Member since</span>
                                    <strong>{{ $sub->created_at ? $sub->created_at->format('M Y') : '2026' }}</strong>
                                </div>
                            </div>

                            @if($sub->isPaused() && $sub->paused_until)
                                <div style="margin-top: 10px; padding: 8px 12px; background: #fdfaf4; border: 1px solid #f6e8cc; border-radius: 6px; font-size: 12px; color: var(--ochre);">
                                    Deliveries on hold until {{ $sub->paused_until->format('d M Y') }}.
                                </div>
                            @endif

                            <div class="member-divider"></div>
                            <p class="member-actions-label">MAKE IT WORK FOR YOUR SEASON</p>

                            @if(!$sub->isCancelled())
                                <div class="portal-actions">
                                    @if($sub->isActive())
                                        <button type="button" class="button button-secondary" onclick="openPortalModal('modal-pause-{{ $sub->id }}')">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                            Pause deliveries
                                        </button>
                                        <button type="button" class="button button-secondary" onclick="openPortalModal('modal-skip-{{ $sub->id }}')">
                                            Skip next month
                                        </button>
                                        @if(isset($availablePlans) && $availablePlans->where('id', '!=', $sub->plan_id)->count() > 0)
                                            <button type="button" class="button button-secondary" onclick="openPortalModal('modal-tier-{{ $sub->id }}')">
                                                Switch membership tier
                                            </button>
                                        @endif
                                    @elseif($sub->isPaused())
                                        <form action="{{ URL::signedRoute('customer.portal.subscription.resume', ['customer' => $customer->id, 'subscription' => $sub->id], now()->addMinutes(15)) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="button button-primary">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                                                Resume deliveries
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="portal-cancel-link" onclick="openPortalModal('modal-cancel-{{ $sub->id }}')">
                                        Cancel membership
                                    </button>
                                </div>
                            @endif

                            <div class="membership-reassurance">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/></svg>
                                <span>Need a little room? Your membership stays just as lovely on pause.</span>
                            </div>

                            <!-- Modal: Pause Subscription -->
                            <div id="modal-pause-{{ $sub->id }}" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
                                <div class="nc-modal-dialog" role="dialog" aria-modal="true">
                                    <div class="nc-modal-header">
                                        <div>
                                            <p class="eyebrow" style="margin-bottom: 3px;">TAKE A SEASON OFF</p>
                                            <h3>Pause Ceramic Deliveries</h3>
                                        </div>
                                        <button type="button" class="nc-btn-close" onclick="closePortalModal('modal-pause-{{ $sub->id }}')" aria-label="Close dialog">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                                        </button>
                                    </div>
                                    <div class="nc-modal-body">
                                        <p style="font-size: 13px; color: var(--nc-charcoal-600); margin-bottom: 16px;">
                                            Taking a little time away? Your Ceramic Club place is saved and your member standing stays just as it is.
                                        </p>
                                        <form action="{{ URL::signedRoute('customer.portal.subscription.pause', ['customer' => $customer->id, 'subscription' => $sub->id], now()->addMinutes(15)) }}" method="POST" class="form-stack">
                                            @csrf
                                            <label class="field-label" for="pause-months-{{ $sub->id }}">
                                                <span>How long would you like to pause?</span>
                                                <select id="pause-months-{{ $sub->id }}" name="months" class="nc-select">
                                                    <option value="1">1 Month (Resume next cycle)</option>
                                                    <option value="2">2 Months</option>
                                                </select>
                                            </label>
                                            <div class="dialog-footer" style="margin-top: 18px;">
                                                <button type="button" class="button button-secondary" onclick="closePortalModal('modal-pause-{{ $sub->id }}')">Keep my membership</button>
                                                <button type="submit" class="button button-primary">Pause deliveries</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal: Skip Month -->
                            <div id="modal-skip-{{ $sub->id }}" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
                                <div class="nc-modal-dialog" role="dialog" aria-modal="true">
                                    <div class="nc-modal-header">
                                        <div>
                                            <p class="eyebrow" style="margin-bottom: 3px;">SKIP THIS CYCLE</p>
                                            <h3>Skip Next Month's Box</h3>
                                        </div>
                                        <button type="button" class="nc-btn-close" onclick="closePortalModal('modal-skip-{{ $sub->id }}')" aria-label="Close dialog">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                                        </button>
                                    </div>
                                    <div class="nc-modal-body">
                                        <div class="notice-soft" style="display: flex; gap: 8px; padding: 12px; background: var(--clay-50); border: 1px solid var(--line); border-radius: 8px; font-size: 12px; color: var(--nc-charcoal-600); margin-bottom: 16px;">
                                            <svg class="icon" style="color: var(--terracotta); flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                            <span>Your upcoming Ceramic Club box won’t be billed. Regular delivery resumes automatically the following month.</span>
                                        </div>
                                        <form action="{{ URL::signedRoute('customer.portal.subscription.skip', ['customer' => $customer->id, 'subscription' => $sub->id], now()->addMinutes(15)) }}" method="POST">
                                            @csrf
                                            <div class="dialog-footer">
                                                <button type="button" class="button button-secondary" onclick="closePortalModal('modal-skip-{{ $sub->id }}')">Keep this month</button>
                                                <button type="submit" class="button button-primary">Confirm Skip</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal: Change Tier -->
                            @if(isset($availablePlans) && $availablePlans->where('id', '!=', $sub->plan_id)->count() > 0)
                                <div id="modal-tier-{{ $sub->id }}" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
                                    <div class="nc-modal-dialog" role="dialog" aria-modal="true">
                                        <div class="nc-modal-header">
                                            <div>
                                                <p class="eyebrow" style="margin-bottom: 3px;">CHOOSE A NEW RHYTHM</p>
                                                <h3>Switch Membership Tier</h3>
                                            </div>
                                            <button type="button" class="nc-btn-close" onclick="closePortalModal('modal-tier-{{ $sub->id }}')" aria-label="Close dialog">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                                            </button>
                                        </div>
                                        <div class="nc-modal-body">
                                            <p style="font-size: 13px; color: var(--nc-charcoal-600); margin-bottom: 14px;">
                                                Find the rhythm that fits your shelf. Stripe will calculate any billing proration automatically.
                                            </p>
                                            <form action="{{ URL::signedRoute('customer.portal.subscription.change_tier', ['customer' => $customer->id, 'subscription' => $sub->id], now()->addMinutes(15)) }}" method="POST" class="form-stack">
                                                @csrf
                                                <label class="field-label" for="tier-plan-select-{{ $sub->id }}">
                                                    <span>Choose a membership</span>
                                                    <select id="tier-plan-select-{{ $sub->id }}" name="plan_id" class="nc-select">
                                                        @foreach($availablePlans->where('id', '!=', $sub->plan_id) as $p)
                                                            <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format($p->deposit_value / 100, 2) }} {{ $sub->shop?->currency ?? 'AUD' }}/mo)</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <div class="dialog-footer" style="margin-top: 18px;">
                                                    <button type="button" class="button button-secondary" onclick="closePortalModal('modal-tier-{{ $sub->id }}')">Keep current tier</button>
                                                    <button type="submit" class="button button-primary">Switch membership</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Modal: Cancel Subscription -->
                            <div id="modal-cancel-{{ $sub->id }}" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
                                <div class="nc-modal-dialog" role="dialog" aria-modal="true">
                                    <div class="nc-modal-header">
                                        <div>
                                            <p class="eyebrow" style="margin-bottom: 3px; color: var(--red);">BEFORE YOU GO</p>
                                            <h3>Cancel Membership</h3>
                                        </div>
                                        <button type="button" class="nc-btn-close" onclick="closePortalModal('modal-cancel-{{ $sub->id }}')" aria-label="Close dialog">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                                        </button>
                                    </div>
                                    <div class="nc-modal-body">
                                        <p style="font-size: 13px; color: var(--nc-charcoal-600); margin-bottom: 16px;">
                                            Your batch allocation for upcoming studio firings will be released. Any already-made pieces and open balances remain visible in your account.
                                        </p>
                                        <form action="{{ URL::signedRoute('customer.portal.subscription.cancel', ['customer' => $customer->id, 'subscription' => $sub->id], now()->addMinutes(15)) }}" method="POST">
                                            @csrf
                                            <div class="dialog-footer">
                                                <button type="button" class="button button-secondary" onclick="closePortalModal('modal-cancel-{{ $sub->id }}')">Keep my membership</button>
                                                <button type="submit" class="button button-danger">End membership</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="panel" style="padding: 24px; text-align: center; color: var(--nc-charcoal-600); font-size: 13px; margin-bottom: 24px;">
                            No active Ceramic Club memberships found.
                        </div>
                    @endforelse

                    <!-- Made To Order Pre-Orders -->
                    <section class="portal-orders">
                        <div class="portal-section-title">
                            <div>
                                <p class="eyebrow">MADE TO ORDER</p>
                                <h2>Your pottery journey</h2>
                            </div>
                        </div>

                        @forelse($orders as $order)
                            @php
                                $balanceCents = $order->remainingBalance();
                                $pendingSchedule = $order->paymentSchedules ? $order->paymentSchedules->where('status', 'pending')->first() : null;
                                $isFullySettled = $balanceCents <= 0;
                            @endphp
                            <article class="portal-order-card">
                                <div class="portal-order-top">
                                    <div>
                                        <span class="order-number">#{{ $order->id }}</span>
                                        <span class="portal-order-date">Ordered {{ $order->created_at ? $order->created_at->format('d M Y') : 'recently' }}</span>
                                    </div>
                                    <span class="status-pill {{ $isFullySettled ? 'status-sage' : 'status-ochre' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                    </span>
                                </div>

                                <div class="portal-order-item">
                                    <span class="portal-pottery-icon">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                                            <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Hand-thrown stoneware piece</strong>
                                        <small>Small-batch · made to order in Melbourne</small>
                                    </div>
                                    <span class="portal-order-total">${{ number_format($order->total_amount / 100, 2) }} AUD</span>
                                </div>

                                <div class="portal-payment-row">
                                    <div>
                                        <span>Deposit paid</span>
                                        <strong>${{ number_format($order->paid_amount / 100, 2) }}</strong>
                                    </div>
                                    <div>
                                        <span>Remaining balance</span>
                                        <strong class="balance-color">${{ number_format($balanceCents / 100, 2) }}</strong>
                                    </div>

                                    @if($balanceCents > 0 && $pendingSchedule)
                                        <a href="{{ URL::signedRoute('pay.balance', ['schedule' => $pendingSchedule->id], now()->addDays(7)) }}" class="button button-primary">
                                            Pay remaining balance
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                                        </a>
                                    @else
                                        <span class="settled-note">✓ Settled in full</span>
                                    @endif
                                </div>

                                <div class="mini-kiln-stage">
                                    <span class="mini-stage-complete">
                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                        Deposit
                                    </span>
                                    <i></i>
                                    <span class="{{ $isFullySettled ? 'mini-stage-complete' : 'mini-stage-active' }}">
                                        @if($isFullySettled)
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                        @else
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                        @endif
                                        In the studio
                                    </span>
                                    <i></i>
                                    <span class="{{ $isFullySettled ? 'mini-stage-active' : '' }}">
                                        On its way
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div class="panel" style="padding: 24px; text-align: center; color: var(--nc-charcoal-600); font-size: 13px;">
                                No recent orders found.
                            </div>
                        @endforelse
                    </section>
                </section>

                <aside class="portal-side-col">
                    <section class="portal-help-card">
                        <span class="portal-help-icon">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>
                            </svg>
                        </span>
                        <p class="eyebrow">WE’RE HERE FOR YOU</p>
                        <h2>A note from the studio.</h2>
                        <p>Questions about your delivery schedule or a piece? Emma is always happy to help.</p>
                        <a href="mailto:emma@northwindceramics.com.au" class="button button-secondary button-full">
                            Write to Emma
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                        </a>
                        <small>emma@northwindceramics.com.au</small>
                    </section>

                    <section class="portal-safe-card">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
                        </svg>
                        <div>
                            <strong>A safe little space.</strong>
                            <p>Only you can see your orders and change your membership from this private signed link.</p>
                            <a class="text-link" href="/portal/expired" style="font-size: 11px;">Request a fresh access link &rarr;</a>
                        </div>
                    </section>
                </aside>
            </div>
        </main>

        <footer class="customer-footer">
            <span>Made with care in Melbourne &amp; Byron Bay.</span>
            <span>Northwind Ceramics © 2026</span>
        </footer>
    </div>

    <script>
    function openPortalModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        document.body.style.overflow = 'hidden';
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => modal.classList.add('is-open'));
    }

    function closePortalModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        document.body.style.overflow = '';
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        setTimeout(() => modal.style.display = 'none', 240);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.nc-modal-scrim.is-open').forEach(m => {
                m.classList.remove('is-open');
                m.setAttribute('aria-hidden', 'true');
                setTimeout(() => m.style.display = 'none', 240);
            });
            document.body.style.overflow = '';
        }
    });
    </script>
</body>
</html>
