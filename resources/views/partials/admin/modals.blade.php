<!-- ============================================== -->
<!-- NORTHWIND CERAMICS ACCESSIBLE MODALS CONTAINER -->
<!-- ============================================== -->

<!-- 1. Polaris Dual-System Refund Modal Dialog (S3-T3) -->
<div id="refund-modal" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
    <div class="nc-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="refund-modal-title">
        <div class="nc-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">STUDIO OWNER ACTION</p>
                <h3 id="refund-modal-title">Issue Order Refund</h3>
            </div>
            <button type="button" class="nc-btn-close" onclick="window.closeRefundModal()" aria-label="Close dialog">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
        <div class="nc-modal-body">
            <div class="refund-warning" style="display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; background: #fff5f2; border: 1px solid #fed7cc; border-radius: 8px; margin-bottom: 16px;">
                <svg class="icon" style="color: var(--terracotta); flex-shrink: 0; margin-top: 2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>
                </svg>
                <span style="font-size: 12px; color: var(--ink); line-height: 1.5;">
                    <strong>Dual-system refund execution:</strong> Processes immediately through Stripe and updates the Shopify order fulfillment ledger. Full refunds automatically cancel pending balance payment schedules.
                </span>
            </div>

            <div class="refund-summary" style="background: var(--clay-50); border: 1px solid var(--line); border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; font-size: 12px; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--nc-charcoal-600);">Shopify Order:</span>
                    <strong id="refund-modal-order-number" style="font-family: var(--mono);">#---</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--nc-charcoal-600);">Customer:</span>
                    <span id="refund-modal-customer" style="font-weight: 500;">---</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--nc-charcoal-600);">Captured via Stripe:</span>
                    <strong id="refund-modal-paid" style="color: var(--sage);">$0.00 AUD</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--nc-charcoal-600);">Previously Refunded:</span>
                    <span id="refund-modal-refunded">$0.00 AUD</span>
                </div>
                <div class="refund-max" style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px dashed var(--line); font-size: 13px;">
                    <span style="font-weight: 600;">Maximum Refundable:</span>
                    <strong id="refund-modal-max" style="color: var(--terracotta); font-family: var(--serif); font-size: 16px;">$0.00 AUD</strong>
                </div>
            </div>

            <form id="refund-form" onsubmit="event.preventDefault(); window.submitRefund();" class="form-stack">
                <input type="hidden" id="refund-order-id" value="">
                
                <label class="field-label" for="refund-amount-cents">
                    <span>Refund Amount <span class="field-optional">(AUD INTEGER CENTS)</span> <span class="required-mark">*</span></span>
                    <input type="number" id="refund-amount-cents" class="nc-input" min="1" required placeholder="e.g. 5000 for $50.00 AUD">
                    <small class="field-hint" id="refund-amount-hint">Enter the exact amount in integer cents. ($1.00 AUD = 100 cents).</small>
                </label>

                <label class="field-label" for="refund-reason">
                    <span>Reason for Refund <span class="required-mark">*</span></span>
                    <select id="refund-reason" class="nc-select">
                        <option value="customer_request">Customer Pre-Order Cancellation</option>
                        <option value="inventory_issue">Studio Material / Kiln Breakage</option>
                        <option value="duplicate">Accidental Duplicate Order</option>
                        <option value="other">Other Studio Reason</option>
                    </select>
                </label>

                <div id="refund-modal-error" style="display: none; padding: 10px 14px; border-radius: 8px; background: #fdebea; color: var(--red); border: 1px solid #f8b4b4; font-size: 12px; margin-top: 4px;"></div>

                <div class="dialog-footer" style="margin-top: 16px;">
                    <button type="button" class="button button-secondary" onclick="window.closeRefundModal()">Cancel</button>
                    <button type="submit" class="button button-danger" id="btn-submit-refund">Confirm &amp; Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Polaris Change Tier Modal Dialog (S5-T2) -->
<div id="modal-change-tier" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
    <div class="nc-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="change-tier-modal-title">
        <div class="nc-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">CERAMIC CLUB</p>
                <h3 id="change-tier-modal-title">Switch Ceramic Club Tier</h3>
            </div>
            <button type="button" class="nc-btn-close" onclick="window.closeChangeTierModal()" aria-label="Close dialog">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
        <div class="nc-modal-body">
            <div class="tier-current" style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: var(--clay-50); border: 1px solid var(--line); border-radius: 8px; margin-bottom: 16px;">
                <span class="tier-swatch" style="display: grid; place-items: center; width: 36px; height: 36px; background: var(--terracotta-wash); border-radius: 8px; color: var(--terracotta);">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/>
                        <path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>
                    </svg>
                </span>
                <div style="flex: 1;">
                    <small style="font-size: 10px; font-weight: 700; color: var(--nc-charcoal-600); text-transform: uppercase; letter-spacing: 0.05em;">CURRENT MEMBERSHIP</small>
                    <input type="text" id="change-tier-current-name" class="nc-input" readonly style="margin-top: 4px; background: #ffffff; font-weight: 600; font-size: 13px; color: var(--ink);">
                </div>
            </div>

            <form id="form-change-tier" onsubmit="event.preventDefault(); window.submitChangeTier();" class="form-stack">
                <input type="hidden" id="change-tier-sub-id" value="">

                <label class="field-label" for="change-tier-plan-select">
                    <span>Select New Membership Tier <span class="required-mark">*</span></span>
                    <select id="change-tier-plan-select" class="nc-select" required>
                        <option value="">Loading available plans...</option>
                    </select>
                    <small class="field-hint">Stripe automatic prorations will calculate credits or additional dues immediately.</small>
                </label>

                <div class="notice-soft" style="display: flex; align-items: flex-start; gap: 8px; padding: 10px 12px; background: var(--clay-50); border: 1px solid var(--line); border-radius: 6px; font-size: 11px; color: var(--nc-charcoal-600);">
                    <svg class="icon" style="color: var(--terracotta); flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/>
                    </svg>
                    <span>Upgrades apply right away. Downgrades take effect at the start of the next delivery billing cycle.</span>
                </div>

                <div id="modal-change-tier-error" style="display: none; padding: 10px 14px; border-radius: 8px; background: #fdebea; color: var(--red); border: 1px solid #f8b4b4; font-size: 12px;"></div>

                <div class="dialog-footer" style="margin-top: 16px;">
                    <button type="button" class="button button-secondary" onclick="window.closeChangeTierModal()">Keep Current Tier</button>
                    <button type="submit" class="button button-primary" id="btn-submit-change-tier">Update Membership Tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Audit Log JSON Inspector Modal -->
<div id="modal-audit-json" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
    <div class="nc-modal-dialog" style="max-width: 640px;" role="dialog" aria-modal="true" aria-labelledby="modal-audit-json-title">
        <div class="nc-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">STUDIO JOURNAL</p>
                <h3 id="modal-audit-json-title">Audit Event Metadata</h3>
            </div>
            <button type="button" class="nc-btn-close" onclick="window.closeAuditJsonModal()" aria-label="Close dialog">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
        <div class="nc-modal-body">
            <div class="json-meta-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #f4efe9; border: 1px solid var(--line); border-radius: 6px 6px 0 0; font-family: var(--mono); font-size: 11px; color: var(--nc-charcoal-600);">
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <i style="width: 7px; height: 7px; border-radius: 50%; background: var(--sage); display: inline-block;"></i>
                    audit-event.json · READ ONLY
                </span>
                <span style="font-size: 10px;">SHA-256 HASH VERIFIED</span>
            </div>
            <pre id="audit-json-content" style="background: var(--clay-50); border: 1px solid var(--line); border-top: 0; border-radius: 0 0 6px 6px; padding: 16px; font-family: var(--mono); font-size: 12px; line-height: 1.6; overflow-x: auto; max-height: 400px; color: var(--ink); margin: 0;"></pre>
        </div>
        <div class="nc-modal-footer">
            <button type="button" class="button button-secondary" onclick="window.closeAuditJsonModal()">Close</button>
        </div>
    </div>
</div>

<!-- 4. Email Template Preview & Test Send Modal -->
<div id="modal-email-preview" class="nc-modal-scrim" style="display: none;" role="presentation" aria-hidden="true">
    <div class="nc-modal-dialog" style="max-width: 760px;" role="dialog" aria-modal="true" aria-labelledby="modal-email-preview-title">
        <div class="nc-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">TRANSACTIONAL EMAIL PREVIEW</p>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 id="modal-email-preview-title" style="margin: 0;">Studio Notification</h3>
                    <span id="email-preview-key-badge" class="status-pill status-clay" style="font-size: 10px;">template-key</span>
                </div>
            </div>
            <button type="button" class="nc-btn-close" onclick="window.closeEmailPreviewModal()" aria-label="Close dialog">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>
        <div class="nc-modal-body">
            <label class="field-label">
                <span>Email Subject</span>
                <input type="text" id="email-preview-subject" class="nc-input" readonly style="background: var(--clay-50);">
            </label>

            <div style="margin-top: 14px;">
                <span style="font-size: 10px; font-weight: 700; color: var(--nc-charcoal-600); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Rendered Studio Email Preview</span>
                <div id="email-preview-frame-container" style="border: 1px solid var(--line); border-radius: 8px; overflow: hidden; background: #FFFFFF; min-height: 280px; max-height: 380px; overflow-y: auto; padding: 20px;">
                    <div id="email-preview-html-body"></div>
                </div>
            </div>

            <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; gap: 10px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label class="field-label" for="email-test-recipient">
                        <span>Send Test Email To:</span>
                        <input type="email" id="email-test-recipient" class="nc-input" placeholder="emma@northwindceramics.com.au">
                    </label>
                </div>
                <button type="button" class="button button-primary" id="btn-send-test-email" onclick="window.sendTestEmail()">Send Test</button>
            </div>
        </div>
        <div class="nc-modal-footer">
            <button type="button" class="button button-secondary" onclick="window.closeEmailPreviewModal()">Close</button>
        </div>
    </div>
</div>
