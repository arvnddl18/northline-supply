<!-- TAB: SETTINGS -->
<div id="tab-settings" class="tab-content" role="tabpanel" aria-labelledby="tab-btn-settings">
    <div class="section-heading">
        <div>
            <p class="eyebrow">STUDIO SETTINGS</p>
            <h1>The practical things, in place.</h1>
            <p class="section-copy">Payment infrastructure, customer notes, and the care we take with every detail.</p>
        </div>
    </div>

    <div class="settings-layout" style="display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(300px, 0.75fr); gap: 20px; align-items: start;">
        <!-- Left Main Settings Column -->
        <div class="settings-main-col" style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Stripe Gateway Card -->
            <section class="panel gateway-panel" style="padding: 24px;">
                <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <p class="eyebrow" style="margin-bottom: 4px;">PAYMENT GATEWAY</p>
                        <h2 style="font: 500 21px/1.2 var(--serif); margin: 0;">Stripe Studio Gateway</h2>
                    </div>
                    <span id="stripe-connection-status" class="status-pill status-ochre">Checking...</span>
                </div>

                <p class="panel-description" style="color: var(--ink-soft); font-size: 13px; line-height: 1.6; margin-bottom: 18px;">
                    Connect your Australian Stripe account to process deposit checkouts, save customer payment methods, and execute off-session balances.
                </p>

                <div id="stripe-alert" style="display: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;"></div>

                <!-- Connected Summary Card -->
                <div id="stripe-connected-summary" style="display: none; background: #faf6f2; border: 1px solid var(--clay-200); border-radius: 10px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="status-dot" style="background: var(--sage);"></span>
                            <strong style="color: var(--sage); font-size: 13px;">Gateway Online &amp; Encrypted</strong>
                        </div>
                        <span id="stripe-account-badge" class="status-pill status-clay" style="font-family: var(--mono); font-size: 11px;">acct_...</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12px;">
                        <div>
                            <span style="display: block; color: var(--ink-soft); font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 2px;">PUBLISHABLE KEY</span>
                            <code id="stripe-pub-masked" style="background: #ffffff; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--line); font-family: var(--mono);">pk_test_...</code>
                        </div>
                        <div>
                            <span style="display: block; color: var(--ink-soft); font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 2px;">SECRET KEY</span>
                            <code style="background: #ffffff; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--line); font-family: var(--mono); color: var(--sage);">•••••••••••••••• (AES-256-GCM)</code>
                        </div>
                    </div>
                </div>

                <!-- Connection Form -->
                <form id="stripe-connect-form" onsubmit="event.preventDefault(); window.connectStripe();" style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="nc-form-group">
                        <label for="stripe-publishable-key" class="field-label" style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Stripe Publishable Key</label>
                        <input type="text" id="stripe-publishable-key" class="nc-input" placeholder="pk_test_..." required style="width: 100%; height: 42px; padding: 0 14px; border: 1px solid var(--clay-200); border-radius: 8px; font-family: var(--mono); font-size: 13px;">
                        <span class="field-hint" style="display: block; color: var(--ink-soft); font-size: 11px; margin-top: 4px;">Used for Stripe Elements client-side checkout and card mounts.</span>
                    </div>

                    <div class="nc-form-group">
                        <label for="stripe-secret-key" class="field-label" style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Stripe Restricted / Secret Key</label>
                        <input type="password" id="stripe-secret-key" class="nc-input" placeholder="sk_test_..." required style="width: 100%; height: 42px; padding: 0 14px; border: 1px solid var(--clay-200); border-radius: 8px; font-family: var(--mono); font-size: 13px;">
                        <span class="field-hint" style="display: block; color: var(--ink-soft); font-size: 11px; margin-top: 4px;">Stored strictly with AES-256-GCM encryption at rest. Never logged or exposed.</span>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 6px;">
                        <button type="submit" class="button button-primary" id="btn-connect-stripe">
                            <span>Save &amp; Verify Credentials</span>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
                        </button>
                        <button type="button" class="button button-secondary button-danger-quiet" id="btn-disconnect-stripe" style="display: none;" onclick="window.disconnectStripe()">
                            <span>Disconnect Gateway</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- Email Notification Templates Studio Card -->
            <section class="panel email-panel" style="padding: 24px;">
                <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <p class="eyebrow" style="margin-bottom: 4px;">CUSTOMER NOTIFICATIONS</p>
                        <h2 style="font: 500 21px/1.2 var(--serif); margin: 0;">Transactional Email Templates</h2>
                    </div>
                    <button class="button button-secondary" type="button" onclick="window.loadEmailTemplates()" style="min-height: 32px; padding: 0 12px; font-size: 11px;">
                        Refresh Templates
                    </button>
                </div>

                <p class="panel-description" style="color: var(--ink-soft); font-size: 13px; line-height: 1.6; margin-bottom: 18px;">
                    Branded pottery studio emails sent to customers for deposits, balance reminders, and dunning notices.
                </p>

                <div class="table-scroll">
                    <table class="data-table" id="email-templates-table">
                        <thead>
                            <tr>
                                <th>EVENT TRIGGER</th>
                                <th>SUBJECT LINE</th>
                                <th>RECIPIENT CONTEXT</th>
                                <th>STATUS</th>
                                <th style="text-align: right;"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody id="email-templates-tbody">
                            <tr>
                                <td>
                                    <strong>Deposit Confirmed</strong>
                                    <small class="cell-subline">order_deposit_confirmed</small>
                                </td>
                                <td>Your Northwind Ceramics Pre-Order #%order% is Confirmed</td>
                                <td>Customer on initial checkout</td>
                                <td><span class="status-pill status-sage">Active</span></td>
                                <td style="text-align: right;">
                                    <button class="table-action" type="button" onclick="window.previewEmailTemplate('order_deposit_confirmed')">
                                        Preview &amp; Test →
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Balance Payment Reminder</strong>
                                    <small class="cell-subline">order_balance_reminder</small>
                                </td>
                                <td>Your Ceramic Order #%order% is Ready for Final Glaze</td>
                                <td>Customer 7 days before ship</td>
                                <td><span class="status-pill status-sage">Active</span></td>
                                <td style="text-align: right;">
                                    <button class="table-action" type="button" onclick="window.previewEmailTemplate('order_balance_reminder')">
                                        Preview &amp; Test →
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Card Update Requested</strong>
                                    <small class="cell-subline">card_update_requested</small>
                                </td>
                                <td>Action Required: Update Payment Method for Order #%order%</td>
                                <td>Customer on card decline</td>
                                <td><span class="status-pill status-ochre">Active</span></td>
                                <td style="text-align: right;">
                                    <button class="table-action" type="button" onclick="window.previewEmailTemplate('card_update_requested')">
                                        Preview &amp; Test →
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Right Side Settings Column -->
        <aside class="settings-side-col" style="display: flex; flex-direction: column; gap: 20px;">
            <section class="panel security-card" style="padding: 24px; background: #fffdfa;">
                <div class="security-emblem" style="display: grid; place-items: center; width: 44px; height: 44px; border-radius: 12px; color: var(--sage); background: var(--sage-light); margin-bottom: 16px;">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 22px; height: 22px;"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/></svg>
                </div>
                <p class="eyebrow" style="margin-bottom: 4px;">CARE IN EVERY STEP</p>
                <h2 style="font: 500 20px/1.2 var(--serif); margin: 0 0 10px;">Customers’ trust is part of the craft.</h2>
                <p style="color: var(--ink-soft); font-size: 12px; line-height: 1.6; margin-bottom: 18px;">
                    Payment credentials are handled with the same care as each piece leaving the kiln.
                </p>

                <div style="display: flex; flex-direction: column; gap: 14px; border-top: 1px solid var(--line); padding-top: 16px;">
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 16px; height: 16px; color: var(--terracotta); flex-shrink: 0; margin-top: 2px;"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/></svg>
                        <div>
                            <strong style="display: block; font-size: 12px;">PCI SAQ-A Boundary</strong>
                            <small style="color: var(--ink-soft); font-size: 11px;">Zero card PAN/CVV touches studio servers.</small>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 16px; height: 16px; color: var(--terracotta); flex-shrink: 0; margin-top: 2px;"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/></svg>
                        <div>
                            <strong style="display: block; font-size: 12px;">AES-256 Encryption</strong>
                            <small style="color: var(--ink-soft); font-size: 11px;">Stripe secret keys encrypted at rest.</small>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 16px; height: 16px; color: var(--terracotta); flex-shrink: 0; margin-top: 2px;"><path d="M4 11a8 8 0 1 1 .6 4.3M4 5v6h6"/><path d="M12 7.5v5l3.5 2"/></svg>
                        <div>
                            <strong style="display: block; font-size: 12px;">Webhook Idempotency</strong>
                            <small style="color: var(--ink-soft); font-size: 11px;">Redis mutex locks prevent duplicate charges.</small>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 8px 12px; border-radius: 6px; background: var(--clay-100); color: var(--ink-soft); font-size: 10px; font-weight: 700; letter-spacing: 0.8px; text-align: center;">
                    PCI SAQ-A VERIFIED SCOPE
                </div>
            </section>

            <section class="panel settings-contact" style="padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; background: #ffffff;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--terracotta)" stroke-width="1.6" style="width: 22px; height: 22px;"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
                    <div>
                        <strong style="display: block; font-size: 12px;">Need a hand?</strong>
                        <small style="color: var(--ink-soft); font-size: 11px;">Emma Carter · Northwind studio</small>
                    </div>
                </div>
                <a href="mailto:emma@northwindceramics.com.au" class="button button-secondary" style="min-height: 32px; padding: 0 10px; font-size: 11px;">
                    Email studio
                </a>
            </section>
        </aside>
    </div>
</div>
