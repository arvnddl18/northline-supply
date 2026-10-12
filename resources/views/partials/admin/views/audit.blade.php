<!-- TAB: AUDIT TRAIL -->
<div id="tab-audit" class="tab-content" role="tabpanel" aria-labelledby="tab-btn-audit">
    <div class="section-heading">
        <div>
            <p class="eyebrow">STUDIO JOURNAL</p>
            <h1>A record of every small step.</h1>
            <p class="section-copy">A quiet, tamper-evident history of financial changes and studio decisions.</p>
        </div>
        <button class="button button-secondary" type="button" onclick="window.loadAuditLogs()">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 7v5h-5M5 17v-5h5"/><path d="M6.5 9a6.5 6.5 0 0 1 11-2L19 8M5 16l1.5 1a6.5 6.5 0 0 0 11-2"/>
            </svg>
            <span>Refresh Log</span>
        </button>
    </div>

    <!-- Audit Toolbar -->
    <div class="audit-toolbar" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 18px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <label class="field-label compact-field" style="display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: 11px; font-weight: 700; color: var(--ink-soft);">
                ACTOR
                <select id="audit-actor-filter" class="nc-select" style="min-width: 170px; height: 38px; padding: 0 12px; border: 1px solid var(--clay-200); border-radius: 8px; background: #ffffff;" onchange="window.loadAuditLogs()">
                    <option value="">All Actors</option>
                    <option value="merchant">Emma Carter (Merchant)</option>
                    <option value="system">Background System</option>
                    <option value="stripe">Stripe Webhooks</option>
                </select>
            </label>
        </div>

        <span class="immutable-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: var(--clay-100); border: 1px solid var(--clay-200); font-size: 10px; font-weight: 700; color: var(--ink-soft); letter-spacing: 0.8px;">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 13px; height: 13px;"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/></svg>
            APPEND-ONLY TRAIL
        </span>
    </div>

    <!-- Audit Table Panel -->
    <div class="panel table-panel audit-table-panel">
        <div class="table-title-row">
            <div>
                <h2>Studio audit trail</h2>
                <span>Timestamps recorded in UTC</span>
            </div>
            <span class="monospace-hint" style="font-family: var(--mono); font-size: 10px; color: var(--ink-soft);">IMMUTABLE LEDGER</span>
        </div>

        <div class="table-scroll">
            <table id="audit-table" class="data-table audit-table">
                <thead>
                    <tr>
                        <th>TIMESTAMP (UTC)</th>
                        <th>ACTION PERFORMED</th>
                        <th>TARGET MODEL</th>
                        <th>ACTOR</th>
                        <th>IP / ORIGIN</th>
                        <th style="text-align: right;">METADATA</th>
                    </tr>
                </thead>
                <tbody id="audit-tbody">
                    @include('partials.admin.components.table-skeleton', ['rows' => 6, 'cols' => 6])
                </tbody>
            </table>
        </div>

        <div class="table-foot" style="display: flex; justify-content: space-between; padding: 14px 20px; border-top: 1px solid var(--line); color: var(--ink-soft); font-size: 11px;">
            <span>Append-only records · Tamper-evident architecture</span>
            <span>Audit Trail Active</span>
        </div>
    </div>

    <div class="audit-footnote" style="display: flex; align-items: center; gap: 8px; margin-top: 20px; font-size: 11px; color: var(--ink-soft);">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 14px; height: 14px;"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/></svg>
        Financial actions and mutations are permanently recorded. Click "JSON" on any entry to inspect raw payloads and before/after diffs.
    </div>
</div>
