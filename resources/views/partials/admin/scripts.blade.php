<script>
(function() {
    'use strict';

    // -------------------------------------------------------------
    // 1. Accessibility & Focus Trapping Dialog Manager
    // -------------------------------------------------------------
    window.dialogManager = {
        activeDialog: null,
        activeTrigger: null,

        openModal: function(modalEl, triggerEl) {
            if (!modalEl) return;
            this.activeTrigger = triggerEl || document.activeElement;
            this.activeDialog = modalEl;
            document.body.style.overflow = 'hidden';
            const mainLayout = document.querySelector('.polaris-layout');
            if (mainLayout) mainLayout.setAttribute('inert', '');
            modalEl.style.display = 'flex';
            modalEl.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                modalEl.classList.add('is-open');
            });
            document.addEventListener('keydown', this.handleKeyDown);
            setTimeout(() => {
                const focusable = modalEl.querySelector('input:not([type="hidden"]), select, textarea, button:not(.nc-btn-close)');
                if (focusable) focusable.focus();
            }, 60);
        },

        closeModal: function(modalEl) {
            if (!modalEl) modalEl = this.activeDialog;
            if (!modalEl) return;
            document.body.style.overflow = '';
            const mainLayout = document.querySelector('.polaris-layout');
            if (mainLayout) mainLayout.removeAttribute('inert');
            modalEl.classList.remove('is-open');
            modalEl.setAttribute('aria-hidden', 'true');
            setTimeout(() => {
                modalEl.style.display = 'none';
            }, 240);
            document.removeEventListener('keydown', this.handleKeyDown);
            if (this.activeTrigger && typeof this.activeTrigger.focus === 'function') {
                this.activeTrigger.focus();
            }
            this.activeDialog = null;
        },

        openDrawer: function(drawerEl, triggerEl) {
            if (!drawerEl) return;
            this.activeTrigger = triggerEl || document.activeElement;
            this.activeDialog = drawerEl;
            document.body.style.overflow = 'hidden';
            const mainLayout = document.querySelector('.polaris-layout');
            if (mainLayout) mainLayout.setAttribute('inert', '');
            drawerEl.style.display = 'block';
            drawerEl.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                drawerEl.classList.add('is-open');
            });
            document.addEventListener('keydown', this.handleKeyDown);
            setTimeout(() => {
                const focusable = drawerEl.querySelector('input:not([type="hidden"]), select, textarea, button:not(.nc-btn-close)');
                if (focusable) focusable.focus();
            }, 60);
        },

        closeDrawer: function(drawerEl) {
            if (!drawerEl) drawerEl = this.activeDialog;
            if (!drawerEl) return;
            document.body.style.overflow = '';
            const mainLayout = document.querySelector('.polaris-layout');
            if (mainLayout) mainLayout.removeAttribute('inert');
            drawerEl.classList.remove('is-open');
            drawerEl.setAttribute('aria-hidden', 'true');
            setTimeout(() => {
                drawerEl.style.display = 'none';
            }, 320);
            document.removeEventListener('keydown', this.handleKeyDown);
            if (this.activeTrigger && typeof this.activeTrigger.focus === 'function') {
                this.activeTrigger.focus();
            }
            this.activeDialog = null;
        },

        handleKeyDown: function(e) {
            const current = window.dialogManager.activeDialog;
            if (!current) return;

            if (e.key === 'Escape') {
                if (current.classList.contains('nc-drawer-container')) {
                    window.dialogManager.closeDrawer(current);
                } else {
                    window.dialogManager.closeModal(current);
                }
                return;
            }

            if (e.key === 'Tab') {
                const focusable = current.querySelectorAll('button, [href], input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])');
                if (!focusable.length) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                } else if (!e.shiftKey && document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        }
    };

    // -------------------------------------------------------------
    // 2. Global State & Currency Formatting
    // -------------------------------------------------------------
    const shopDomain = "{{ $shop }}";
    const apiKey = "{{ $apiKey }}";
    const currency = "{{ $currency }}";
    let activeTab = 'overview';
    let currentOrderPage = 1;
    let currentOrderStatus = '';
    let currentOrderSearch = '';
    let searchDebounceTimer = null;
    let selectedOrderForDrawer = null;

    function formatAud(cents) {
        const val = Number(cents || 0) / 100;
        return '$' + val.toLocaleString('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '\u00A0' + currency;
    }

    // -------------------------------------------------------------
    // 3. API Communication with Session Token Management
    // -------------------------------------------------------------
    async function getSessionToken() {
        if (window.shopify && typeof window.shopify.idToken === 'function') {
            try {
                const token = await window.shopify.idToken();
                if (token) return token;
            } catch (err) {
                console.warn('Shopify App Bridge idToken error:', err);
            }
        }
        const metaToken = document.querySelector('meta[name="shopify-session-token"]')?.content;
        if (metaToken) return metaToken;
        return 'test_session_token_' + shopDomain;
    }

    window.fetchApi = async function(url, options = {}) {
        options.headers = options.headers || {};
        options.headers['Accept'] = 'application/json';
        options.headers['Content-Type'] = 'application/json';

        const token = await getSessionToken();
        if (token) {
            options.headers['Authorization'] = 'Bearer ' + token;
        }

        const separator = url.includes('?') ? '&' : '?';
        const finalUrl = url.includes('shop=') ? url : `${url}${separator}shop=${encodeURIComponent(shopDomain)}`;

        const response = await fetch(finalUrl, options);
        if (!response.ok) {
            let errorMsg = `HTTP ${response.status}`;
            try {
                const errData = await response.json();
                if (errData.message) errorMsg = errData.message;
                if (errData.error) errorMsg = errData.error;
            } catch (e) {}
            throw new Error(errorMsg);
        }
        return await response.json();
    };

    // -------------------------------------------------------------
    // 4. Tab Navigation with Emil Crossfades
    // -------------------------------------------------------------
    window.navigateTab = function(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.nav-tab').forEach(el => {
            el.classList.remove('active');
            el.setAttribute('aria-selected', 'false');
        });

        const targetView = document.getElementById('tab-' + tabName);
        const targetBtn = document.getElementById('tab-btn-' + tabName);

        if (targetView && targetBtn) {
            targetView.classList.add('active');
            targetBtn.classList.add('active');
            targetBtn.setAttribute('aria-selected', 'true');
            activeTab = tabName;
            window.location.hash = tabName;

            const tabTitles = {
                overview: 'Overview',
                plans: 'Payment plans',
                orders: 'Orders ledger',
                subscriptions: 'Ceramic Club',
                audit: 'Studio audit',
                settings: 'Settings'
            };
            const breadcrumbEl = document.getElementById('active-tab-breadcrumb');
            if (breadcrumbEl && tabTitles[tabName]) {
                breadcrumbEl.textContent = tabTitles[tabName];
            }

            // Trigger Lazy Data Loads
            if (tabName === 'overview') window.refreshOverview();
            else if (tabName === 'plans') window.loadPlans();
            else if (tabName === 'orders') window.loadOrders();
            else if (tabName === 'subscriptions') window.loadSubscriptions();
            else if (tabName === 'audit') window.loadAuditLogs();
            else if (tabName === 'settings') {
                window.checkStripeStatus();
                window.loadEmailTemplates();
            }
        }
    };

    // -------------------------------------------------------------
    // 5. Overview & KPI Dashboard
    // -------------------------------------------------------------
    window.refreshOverview = async function() {
        try {
            // Dashboard KPI metrics
            const dash = await window.fetchApi('/api/dashboard');
            const kpis = (dash && (dash.kpis || dash.kpi_metrics)) ? (dash.kpis || dash.kpi_metrics) : null;
            if (kpis) {
                const collected = kpis.total_collected_cents ?? kpis.collected_cash_cents ?? 0;
                const balanceDue = kpis.pending_balance_due_cents ?? kpis.pending_balance_cents ?? 0;
                const upcoming = kpis.upcoming_scheduled_charges?.total_cents ?? kpis.upcoming_charges_14d_cents ?? 0;
                const failed = kpis.failed_payments?.total_cents ?? kpis.failed_charges_cents ?? 0;

                document.getElementById('kpi-collected').innerText = formatAud(collected);
                document.getElementById('kpi-balance-due').innerText = formatAud(balanceDue);
                document.getElementById('kpi-upcoming').innerText = formatAud(upcoming);
                document.getElementById('kpi-failed').innerText = formatAud(failed);
            }

            // Onboarding checklist
            const overview = await window.fetchApi('/api/overview');
            if (overview && overview.checklist) {
                const chk = overview.checklist;
                const stripeIcon = document.getElementById('chk-stripe-icon');
                const planIcon = document.getElementById('chk-plan-icon');

                if (chk.stripe_connected) {
                    stripeIcon.className = 'checklist-icon done';
                    stripeIcon.style.background = 'var(--nc-sage-500)';
                    stripeIcon.style.color = '#fff';
                    stripeIcon.innerHTML = '&#10003;';
                }

                if (chk.plans_created) {
                    planIcon.className = 'checklist-icon done';
                    planIcon.style.background = 'var(--nc-sage-500)';
                    planIcon.style.color = '#fff';
                    planIcon.innerHTML = '&#10003;';
                }

                const allDone = chk.shopify_installed && chk.stripe_connected && chk.plans_created;
                const statusBadge = document.getElementById('chk-overall-status');
                if (statusBadge) {
                    if (allDone) {
                        statusBadge.className = 'badge badge-success';
                        statusBadge.innerText = 'Ready for Pre-Orders';
                    } else {
                        statusBadge.className = 'badge badge-warning';
                        statusBadge.innerText = 'Action Required';
                    }
                }
            }
        } catch (err) {
            console.error('Error refreshing overview:', err);
        }
    };

    // -------------------------------------------------------------
    // 6. Plan Management & Slide-Over Drawer
    // -------------------------------------------------------------
    window.openPlanModal = async function(planId) {
        const drawer = document.getElementById('nc-plan-drawer');
        const titleEl = document.getElementById('plan-drawer-title');
        const form = document.getElementById('plan-form');
        form.reset();
        document.getElementById('plan-edit-id').value = '';

        if (planId) {
            titleEl.innerText = 'Edit Payment Plan';
            try {
                const res = await window.fetchApi('/api/plans/' + planId);
                const plan = res.data;
                document.getElementById('plan-edit-id').value = plan.id;
                document.getElementById('plan-name-input').value = plan.name;
                document.getElementById('plan-type-select').value = plan.type;
                document.getElementById('plan-deposit-type').value = plan.deposit_type;
                document.getElementById('plan-deposit-value').value = plan.deposit_type === 'percentage' ? plan.deposit_value : plan.deposit_value;
                document.getElementById('plan-active-input').checked = !!plan.is_active;

                if (plan.installments_count) {
                    document.getElementById('plan-installments-count').value = plan.installments_count;
                }
                if (plan.installment_frequency) {
                    document.getElementById('plan-installment-frequency').value = plan.installment_frequency;
                }

                if (plan.targets && plan.targets.length) {
                    const prodTargets = plan.targets.filter(t => t.target_type === 'product').map(t => t.target_id).join(', ');
                    const colTargets = plan.targets.filter(t => t.target_type === 'collection').map(t => t.target_id).join(', ');
                    document.getElementById('plan-product-targets').value = prodTargets;
                    document.getElementById('plan-collection-targets').value = colTargets;
                }
            } catch (err) {
                window.showToast('Failed to load plan: ' + err.message, 'error');
            }
        } else {
            titleEl.innerText = 'Create Deposit Plan';
            document.getElementById('plan-active-input').checked = true;
        }

        window.togglePlanTypeFields();
        window.updatePlanPreview();
        window.dialogManager.openDrawer(drawer);
    };

    window.closePlanModal = function() {
        const drawer = document.getElementById('nc-plan-drawer');
        window.dialogManager.closeDrawer(drawer);
    };

    window.togglePlanTypeFields = function() {
        const type = document.getElementById('plan-type-select').value;
        const depositFields = document.getElementById('plan-deposit-settings');
        const installmentFields = document.getElementById('plan-installment-settings');

        if (type === 'installment') {
            depositFields.style.display = 'none';
            installmentFields.style.display = 'grid';
        } else {
            depositFields.style.display = 'grid';
            installmentFields.style.display = 'none';
        }
    };

    window.updatePlanPreview = function() {
        const type = document.getElementById('plan-type-select').value;
        const sampleCents = 20000; // $200.00 AUD

        if (type === 'deposit') {
            document.getElementById('preview-deposit-container').style.display = 'flex';
            document.getElementById('preview-installment-container').style.display = 'none';

            const method = document.getElementById('plan-deposit-type').value;
            const val = Number(document.getElementById('plan-deposit-value').value) || 0;
            let depositCents = 0;

            if (method === 'percentage') {
                depositCents = Math.round(sampleCents * (val / 100));
            } else {
                depositCents = val;
            }

            const balanceCents = Math.max(0, sampleCents - depositCents);
            document.getElementById('preview-deposit-label').innerText = formatAud(depositCents);
            document.getElementById('preview-balance-label').innerText = formatAud(balanceCents);
        } else {
            document.getElementById('preview-deposit-container').style.display = 'none';
            document.getElementById('preview-installment-container').style.display = 'flex';

            const count = Number(document.getElementById('plan-installments-count').value) || 3;
            const splitCents = Math.floor(sampleCents / count);
            const remainder = sampleCents % count;

            let html = '';
            for (let i = 1; i <= count; i++) {
                const amt = i === 1 ? splitCents + remainder : splitCents;
                html += `<div style="display:flex; justify-content:space-between;"><span>Payment #${i} (Due ${i === 1 ? 'Today' : 'Cycle ' + i}):</span><strong>${formatAud(amt)}</strong></div>`;
            }
            document.getElementById('preview-installment-list').innerHTML = html;
        }
    };

    window.savePlan = async function() {
        const planId = document.getElementById('plan-edit-id').value;
        const type = document.getElementById('plan-type-select').value;
        const isInstallment = type === 'installment';

        const payload = {
            name: document.getElementById('plan-name-input').value,
            type: type,
            deposit_type: isInstallment ? 'percentage' : document.getElementById('plan-deposit-type').value,
            deposit_value: isInstallment ? Math.round(100 / (Number(document.getElementById('plan-installments-count').value) || 3)) : Number(document.getElementById('plan-deposit-value').value),
            is_active: document.getElementById('plan-active-input').checked
        };

        if (isInstallment) {
            payload.installments_count = Number(document.getElementById('plan-installments-count').value);
            payload.installment_frequency = document.getElementById('plan-installment-frequency').value;
        }

        const productTargets = document.getElementById('plan-product-targets').value.split(',').map(s => s.trim()).filter(Boolean);
        const collectionTargets = document.getElementById('plan-collection-targets').value.split(',').map(s => s.trim()).filter(Boolean);

        const targets = [];
        productTargets.forEach(id => targets.push({ target_type: 'product', target_id: id }));
        collectionTargets.forEach(id => targets.push({ target_type: 'collection', target_id: id }));
        payload.targets = targets;

        const saveBtn = document.getElementById('btn-save-plan');
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';

        try {
            if (planId) {
                await window.fetchApi('/api/plans/' + planId, { method: 'PUT', body: JSON.stringify(payload) });
                window.showToast('Plan updated successfully', 'success');
            } else {
                await window.fetchApi('/api/plans', { method: 'POST', body: JSON.stringify(payload) });
                window.showToast('Deposit plan created successfully', 'success');
            }
            window.closePlanModal();
            window.loadPlans();
        } catch (err) {
            window.showToast('Error saving plan: ' + err.message, 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Save Plan';
        }
    };

    window.deletePlan = async function(planId) {
        if (!confirm('Are you sure you want to deactivate and remove this payment plan?')) return;
        try {
            await window.fetchApi('/api/plans/' + planId, { method: 'DELETE' });
            window.showToast('Plan deleted successfully', 'success');
            window.loadPlans();
        } catch (err) {
            window.showToast('Error deleting plan: ' + err.message, 'error');
        }
    };

    window.loadPlans = async function() {
        try {
            const res = await window.fetchApi('/api/plans');
            const plans = res.plans || res.data || [];
            const tbody = document.getElementById('plans-tbody');
            const emptyState = document.getElementById('plans-empty-state');
            const table = document.getElementById('plans-table');

            if (!plans.length) {
                emptyState.style.display = 'block';
                table.style.display = 'none';
                return;
            }

            emptyState.style.display = 'none';
            table.style.display = 'table';
            tbody.innerHTML = '';

            plans.forEach(plan => {
                const tr = document.createElement('tr');
                const isPct = plan.deposit_type === 'percentage';
                const depositStr = isPct ? `${plan.deposit_value}%` : formatAud(plan.deposit_value);
                const targetCount = (plan.targets && plan.targets.length) ? `${plan.targets.length} target(s)` : 'Shop-Wide All';

                tr.innerHTML = `
                    <td><strong>${plan.name}</strong></td>
                    <td><span class="badge badge-info">${plan.type}</span></td>
                    <td class="cell-currency"><strong style="color: var(--nc-terracotta-500);">${depositStr}</strong></td>
                    <td>${targetCount}</td>
                    <td><span class="badge ${plan.is_active ? 'badge-success' : 'badge-neutral'}">${plan.is_active ? 'Active' : 'Disabled'}</span></td>
                    <td style="text-align: right;">
                        <button class="btn btn-secondary" style="min-height: 32px; padding: 4px 12px;" aria-label="Edit ${plan.name}" onclick="window.openPlanModal(${plan.id})">Edit</button>
                        <button class="btn btn-danger" style="min-height: 32px; padding: 4px 12px; margin-left: 6px;" aria-label="Delete ${plan.name}" onclick="window.deletePlan(${plan.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (err) {
            console.error('Error loading plans:', err);
        }
    };

    // -------------------------------------------------------------
    // 7. Orders Ledger & Slide-Over Inspector
    // -------------------------------------------------------------
    window.debounceSearchOrders = function(query) {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            currentOrderSearch = query;
            currentOrderPage = 1;
            window.loadOrders();
        }, 300);
    };

    window.filterOrderStatus = function(status) {
        currentOrderStatus = status;
        currentOrderPage = 1;

        document.querySelectorAll('#order-status-filters button').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-status') === status);
        });

        window.loadOrders();
    };

    window.changeOrderPage = function(delta) {
        currentOrderPage += delta;
        window.loadOrders();
    };

    window.loadOrders = async function() {
        const tbody = document.getElementById('orders-tbody');
        tbody.innerHTML = `
            @include('partials.admin.components.table-skeleton', ['rows' => 4, 'cols' => 7])
        `;

        try {
            let url = `/api/orders?page=${currentOrderPage}`;
            if (currentOrderStatus) url += `&status=${encodeURIComponent(currentOrderStatus)}`;
            if (currentOrderSearch) url += `&search=${encodeURIComponent(currentOrderSearch)}`;

            const res = await window.fetchApi(url);
            const orders = res.data || [];
            const meta = res.meta || {};

            tbody.innerHTML = '';
            if (!orders.length) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 32px; color: var(--p-color-text-subdued);">No orders matching filter criteria.</td></tr>`;
                document.getElementById('orders-page-info').innerText = 'No orders found';
                document.getElementById('orders-page-prev').disabled = true;
                document.getElementById('orders-page-next').disabled = true;
                return;
            }

            orders.forEach(order => {
                const tr = document.createElement('tr');
                tr.className = 'row-interactive';
                tr.onclick = (e) => {
                    // Ignore clicks on buttons inside row
                    if (e.target.closest('button')) return;
                    window.inspectOrder(order.id);
                };

                const rawStatus = order.payment_status || order.status || 'unknown';
                const statusLabel = order.status_label || (rawStatus.replace(/_/g, ' '));
                const balanceDue = order.remaining_balance ?? order.balance_due ?? 0;
                const orderNum = order.shopify_order_id ? `#${order.shopify_order_id}` : (order.shopify_order_number ? `#${order.shopify_order_number}` : `#ORD-${order.id}`);
                const customerEmail = order.customer_email || (order.customer ? order.customer.email : '—');
                const encodedOrder = encodeURIComponent(JSON.stringify(order));

                let badgeClass = 'badge-warning';
                if (rawStatus === 'paid') badgeClass = 'badge-success';
                else if (rawStatus === 'payment_failed' || rawStatus === 'failed' || rawStatus === 'refunded' || rawStatus === 'disputed') badgeClass = 'badge-danger';
                else if (rawStatus === 'deposit_paid') badgeClass = 'badge-warning';
                else badgeClass = 'badge-neutral';

                tr.innerHTML = `
                    <td><strong>${orderNum}</strong></td>
                    <td>${customerEmail}</td>
                    <td><span class="badge ${badgeClass}">${statusLabel}</span></td>
                    <td class="cell-currency"><strong>${formatAud(order.total_amount)}</strong></td>
                    <td class="cell-currency" style="color: var(--nc-sage-500);">${formatAud(order.paid_amount)}</td>
                    <td class="cell-currency" style="color: var(--nc-ochre-700); font-weight: 600;">${formatAud(balanceDue)}</td>
                    <td style="text-align: right;">
                        <button class="btn btn-secondary" style="min-height: 32px; padding: 4px 10px; font-size: 12px;" aria-label="Copy balance link for order ${orderNum}" onclick="window.generatePaymentLink(${order.id})">Link</button>
                        <button class="btn btn-secondary" style="min-height: 32px; padding: 4px 10px; font-size: 12px; margin-left: 6px;" aria-label="Issue refund for order ${orderNum}" onclick="window.openRefundModalDecoded('${encodedOrder}')">Refund</button>
                        <button class="btn btn-secondary" style="min-height: 32px; padding: 4px 10px; font-size: 12px; margin-left: 6px;" aria-label="Inspect schedule for order ${orderNum}" onclick="window.inspectOrder(${order.id})">Inspect &rarr;</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update Failed Count in Nav Badge and Filter Pill
            const failedCount = orders.filter(o => {
                const s = o.payment_status || o.status || '';
                return s === 'payment_failed' || s === 'failed';
            }).length;
            const navFailedBadge = document.getElementById('nav-failed-count');
            if (navFailedBadge) {
                if (failedCount > 0) {
                    navFailedBadge.innerText = failedCount;
                    navFailedBadge.style.display = 'inline-flex';
                } else {
                    navFailedBadge.style.display = 'none';
                }
            }
            const filterFailedBadge = document.getElementById('order-filter-failed-count');
            if (filterFailedBadge) {
                if (failedCount > 0) {
                    filterFailedBadge.innerText = failedCount;
                    filterFailedBadge.style.display = 'inline-block';
                } else {
                    filterFailedBadge.style.display = 'none';
                }
            }

            // Pagination update
            const total = meta.total || orders.length;
            const from = meta.from || ((currentOrderPage - 1) * 15 + 1);
            const to = meta.to || from + orders.length - 1;
            document.getElementById('orders-page-info').innerText = `Showing ${from}–${to} of ${total} orders`;
            document.getElementById('orders-page-prev').disabled = currentOrderPage <= 1;
            document.getElementById('orders-page-next').disabled = meta.last_page ? currentOrderPage >= meta.last_page : orders.length < 15;
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--nc-red-500);">Error loading orders: ${err.message}</td></tr>`;
        }
    };

    // -------------------------------------------------------------
    // 8. Order Inspector Slide-Over Sheet
    // -------------------------------------------------------------
    window.inspectOrder = async function(orderId) {
        const drawer = document.getElementById('nc-order-drawer');
        const loading = document.getElementById('order-drawer-loading');
        const content = document.getElementById('order-drawer-content');

        loading.style.display = 'block';
        content.style.display = 'none';
        window.dialogManager.openDrawer(drawer);

        try {
            const res = await window.fetchApi(`/api/orders/${orderId}`);
            const order = res.order || res.data;
            if (!order) throw new Error('Order data could not be retrieved.');
            selectedOrderForDrawer = order;

            const orderNum = order.shopify_order_id ? `#${order.shopify_order_id}` : (order.shopify_order_number ? `#${order.shopify_order_number}` : `#ORD-${order.id}`);
            const rawStatus = order.payment_status || order.status || 'deposit_paid';
            const statusLabel = order.status_label || rawStatus.replace(/_/g, ' ');
            const balanceDue = order.remaining_balance ?? order.balance_due ?? 0;
            const customerEmail = (order.customer && order.customer.email) ? order.customer.email : (order.customer_email || '—');
            const stripeCustId = (order.customer && order.customer.stripe_customer_id) ? order.customer.stripe_customer_id : '—';

            document.getElementById('order-drawer-order-number').innerText = orderNum;
            document.getElementById('drawer-total-amount').innerText = formatAud(order.total_amount);
            document.getElementById('drawer-paid-amount').innerText = formatAud(order.paid_amount);
            document.getElementById('drawer-balance-due').innerText = formatAud(balanceDue);
            document.getElementById('drawer-customer-email').innerText = customerEmail;
            document.getElementById('drawer-stripe-customer-id').innerText = stripeCustId;

            const statusBadge = document.getElementById('order-drawer-status-badge');
            statusBadge.innerText = statusLabel.toUpperCase();
            statusBadge.className = `badge ${rawStatus === 'paid' ? 'badge-success' : (rawStatus === 'failed' || rawStatus === 'disputed' || rawStatus === 'refunded' ? 'badge-danger' : 'badge-warning')}`;

            // Render Payment Progression Timeline
            const schedContainer = document.getElementById('drawer-schedules-container');
            schedContainer.innerHTML = '';
            const schedules = order.schedules || order.payment_schedules || [];
            if (schedules.length) {
                schedules.forEach((sch, idx) => {
                    const isDone = sch.status === 'succeeded';
                    const isFailed = sch.status === 'failed';
                    const dueDateStr = sch.due_at ? new Date(sch.due_at).toLocaleDateString('en-AU', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Estimated Kiln Completion';

                    schedContainer.innerHTML += `
                        <div class="nc-timeline-item">
                            <div class="nc-timeline-dot ${isDone ? 'done' : 'pending'}"></div>
                            <div class="nc-timeline-content">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div class="nc-timeline-title">Stage #${sch.schedule_sequence || idx + 1}: ${sch.status}</div>
                                    <strong class="cell-currency">${formatAud(sch.amount)}</strong>
                                </div>
                                <div class="nc-timeline-meta">Scheduled Due: ${dueDateStr} &bull; Retries: ${sch.attempt_count || 0}</div>
                                ${sch.failure_reason ? `<div style="color:var(--nc-red-500); font-size:12px; margin-top:2px;">Failure: ${sch.failure_reason}</div>` : ''}
                            </div>
                        </div>
                    `;
                });
            } else {
                schedContainer.innerHTML = '<p style="color:var(--p-color-text-subdued); font-size:13px;">No remaining payment schedules for this order.</p>';
            }

            // Render Payments list
            const payList = document.getElementById('drawer-payments-list');
            payList.innerHTML = '';
            if (order.payments && order.payments.length) {
                order.payments.forEach(p => {
                    payList.innerHTML += `
                        <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--nc-clay-100);">
                            <div>
                                <strong>${p.payment_type || 'Payment'}</strong>
                                <div style="color:var(--p-color-text-subdued); font-size:11px; font-family:monospace;">${p.stripe_payment_intent_id || 'Intent'}</div>
                            </div>
                            <div style="text-align:right;">
                                <strong>${formatAud(p.amount)}</strong>
                                <div style="color:var(--nc-sage-500); font-size:11px;">${p.status}</div>
                            </div>
                        </div>
                    `;
                });
            } else {
                payList.innerHTML = '<p style="color:var(--p-color-text-subdued);">No recorded Stripe transactions yet.</p>';
            }

            // Render Email logs
            const emailList = document.getElementById('drawer-emails-list');
            emailList.innerHTML = '';
            if (order.email_logs && order.email_logs.length) {
                order.email_logs.forEach(em => {
                    emailList.innerHTML += `<div>&bull; ${em.template_key} sent to ${em.recipient_email} at ${new Date(em.created_at).toLocaleString()}</div>`;
                });
            } else {
                emailList.innerHTML = '<p>No customer notifications dispatched yet.</p>';
            }

            loading.style.display = 'none';
            content.style.display = 'block';
        } catch (err) {
            loading.innerHTML = `<p style="color:var(--nc-red-500);">Failed to load order: ${err.message}</p>`;
        }
    };

    window.closeOrderDrawer = function() {
        const drawer = document.getElementById('nc-order-drawer');
        window.dialogManager.closeDrawer(drawer);
    };

    window.drawerCopyPaymentLink = function() {
        if (selectedOrderForDrawer) {
            window.generatePaymentLink(selectedOrderForDrawer.id);
        }
    };

    window.drawerOpenRefund = function() {
        if (selectedOrderForDrawer) {
            window.openRefundModal(selectedOrderForDrawer);
        }
    };

    window.generatePaymentLink = async function(orderId) {
        try {
            const res = await window.fetchApi(`/api/orders/${orderId}/payment-link`, { method: 'POST' });
            if (res.payment_url) {
                navigator.clipboard.writeText(res.payment_url);
                window.showToast('Balance payment link copied to clipboard!', 'success');
            }
        } catch (err) {
            window.showToast('Could not generate payment link: ' + err.message, 'error');
        }
    };

    // -------------------------------------------------------------
    // 9. Dual-System Refunds
    // -------------------------------------------------------------
    window.openRefundModalDecoded = function(encodedOrder) {
        try {
            const order = JSON.parse(decodeURIComponent(encodedOrder));
            window.openRefundModal(order);
        } catch (e) {
            console.error('Failed to parse order for refund:', e);
        }
    };

    window.openRefundModal = function(order) {
        const modal = document.getElementById('refund-modal');
        document.getElementById('refund-order-id').value = order.id;
        document.getElementById('refund-modal-order-number').innerText = '#' + (order.shopify_order_id || order.shopify_order_number || order.id);
        document.getElementById('refund-modal-customer').innerText = order.customer_email || (order.customer ? order.customer.email : '—');
        document.getElementById('refund-modal-paid').innerText = formatAud(order.paid_amount);
        document.getElementById('refund-modal-refunded').innerText = formatAud(order.total_refunded || 0);

        const maxRefundable = Math.max(0, (order.paid_amount || 0) - (order.total_refunded || 0));
        document.getElementById('refund-modal-max').innerText = formatAud(maxRefundable);
        document.getElementById('refund-amount-cents').value = maxRefundable;
        document.getElementById('refund-amount-cents').max = maxRefundable;
        document.getElementById('refund-modal-error').style.display = 'none';

        window.dialogManager.openModal(modal);
    };

    window.closeRefundModal = function() {
        const modal = document.getElementById('refund-modal');
        window.dialogManager.closeModal(modal);
    };

    window.submitRefund = async function() {
        const orderId = document.getElementById('refund-order-id').value;
        const amountCents = Number(document.getElementById('refund-amount-cents').value);
        const reason = document.getElementById('refund-reason').value;
        const errorEl = document.getElementById('refund-modal-error');
        const submitBtn = document.getElementById('btn-submit-refund');

        errorEl.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerText = 'Processing Refund...';

        try {
            await window.fetchApi(`/api/orders/${orderId}/refund`, {
                method: 'POST',
                body: JSON.stringify({ amount_cents: amountCents, reason: reason })
            });
            window.showToast('Refund processed successfully across Stripe and Shopify', 'success');
            window.closeRefundModal();
            window.loadOrders();
            if (activeTab === 'overview') window.refreshOverview();
        } catch (err) {
            errorEl.innerText = err.message;
            errorEl.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Confirm & Process Refund';
        }
    };

    // -------------------------------------------------------------
    // 10. Subscriptions & Tier Switching
    // -------------------------------------------------------------
    window.loadSubscriptions = async function() {
        try {
            const res = await window.fetchApi('/api/subscriptions');
            const subs = res.data || [];
            const tbody = document.getElementById('subscriptions-tbody');
            tbody.innerHTML = '';

            let activeCount = 0;
            let pausedCount = 0;
            let mrrCents = 0;

            if (!subs.length) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:32px; color:var(--p-color-text-subdued);">No Ceramic Club subscriptions found.</td></tr>`;
                return;
            }

            subs.forEach(sub => {
                if (sub.status === 'active') {
                    activeCount++;
                    mrrCents += (sub.plan ? sub.plan.deposit_value : 0);
                } else if (sub.status === 'paused') {
                    pausedCount++;
                }

                const tr = document.createElement('tr');
                const renewalDate = sub.next_renewal_at ? new Date(sub.next_renewal_at).toLocaleDateString('en-AU', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
                const planName = sub.plan ? sub.plan.name : 'Clay Membership';
                const planPrice = sub.plan ? formatAud(sub.plan.deposit_value) : '$0.00';

                let actions = [];
                if (sub.status === 'active') {
                    actions.push(`<button class="btn btn-secondary" style="min-height:28px; padding:2px 8px; font-size:11px;" onclick="window.pauseSubscription(${sub.id}, 1)">Pause</button>`);
                    actions.push(`<button class="btn btn-secondary" style="min-height:28px; padding:2px 8px; font-size:11px;" onclick="window.skipSubscription(${sub.id})">Skip</button>`);
                    actions.push(`<button class="btn btn-secondary" style="min-height:28px; padding:2px 8px; font-size:11px;" onclick="window.openChangeTierModal(${sub.id}, ${sub.plan_id})">Tier</button>`);
                    actions.push(`<button class="btn btn-danger" style="min-height:28px; padding:2px 8px; font-size:11px;" onclick="window.cancelSubscription(${sub.id})">Cancel</button>`);
                } else if (sub.status === 'paused') {
                    actions.push(`<button class="btn btn-primary" style="min-height:28px; padding:2px 8px; font-size:11px;" onclick="window.resumeSubscription(${sub.id})">Resume</button>`);
                    actions.push(`<button class="btn btn-danger" style="min-height:28px; padding:2px 8px; font-size:11px;" onclick="window.cancelSubscription(${sub.id})">Cancel</button>`);
                }

                tr.innerHTML = `
                    <td><strong>${sub.customer ? sub.customer.email : 'Member'}</strong></td>
                    <td>${planName}</td>
                    <td>${planPrice} / month</td>
                    <td><span class="badge ${sub.status === 'active' ? 'badge-success' : (sub.status === 'paused' ? 'badge-neutral' : 'badge-danger')}">${sub.status}</span></td>
                    <td>${renewalDate}</td>
                    <td style="text-align: right; display: flex; gap: 4px; justify-content: flex-end;">${actions.join('')}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('sub-kpi-active').innerText = activeCount;
            document.getElementById('sub-kpi-mrr').innerText = formatAud(mrrCents);
            document.getElementById('sub-kpi-paused').innerText = pausedCount;
        } catch (err) {
            console.error('Error loading subscriptions:', err);
        }
    };

    window.pauseSubscription = async function(id, months) {
        try {
            await window.fetchApi(`/api/subscriptions/${id}/pause`, { method: 'POST', body: JSON.stringify({ months: months }) });
            window.showToast('Subscription paused', 'success');
            window.loadSubscriptions();
        } catch (err) {
            window.showToast('Error pausing: ' + err.message, 'error');
        }
    };

    window.resumeSubscription = async function(id) {
        try {
            await window.fetchApi(`/api/subscriptions/${id}/resume`, { method: 'POST' });
            window.showToast('Subscription resumed', 'success');
            window.loadSubscriptions();
        } catch (err) {
            window.showToast('Error resuming: ' + err.message, 'error');
        }
    };

    window.skipSubscription = async function(id) {
        try {
            await window.fetchApi(`/api/subscriptions/${id}/skip`, { method: 'POST' });
            window.showToast('Skipped next delivery cycle', 'success');
            window.loadSubscriptions();
        } catch (err) {
            window.showToast('Error skipping: ' + err.message, 'error');
        }
    };

    window.cancelSubscription = async function(id) {
        if (!confirm('Are you sure you want to permanently cancel this membership in Stripe and Shopify?')) return;
        try {
            await window.fetchApi(`/api/subscriptions/${id}/cancel`, { method: 'POST' });
            window.showToast('Subscription cancelled', 'success');
            window.loadSubscriptions();
        } catch (err) {
            window.showToast('Error cancelling: ' + err.message, 'error');
        }
    };

    window.openChangeTierModal = async function(subId, currentPlanId) {
        const modal = document.getElementById('modal-change-tier');
        document.getElementById('change-tier-sub-id').value = subId;
        const select = document.getElementById('change-tier-plan-select');
        select.innerHTML = '<option value="">Loading membership plans...</option>';
        document.getElementById('modal-change-tier-error').style.display = 'none';

        try {
            const res = await window.fetchApi('/api/plans?type=subscription');
            const plans = res.data || [];
            select.innerHTML = '';
            plans.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.innerText = `${p.name} — ${formatAud(p.deposit_value)}/month`;
                if (p.id === currentPlanId) {
                    opt.selected = true;
                    document.getElementById('change-tier-current-name').value = p.name;
                }
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('Error fetching plans for tier switch:', e);
        }

        window.dialogManager.openModal(modal);
    };

    window.closeChangeTierModal = function() {
        const modal = document.getElementById('modal-change-tier');
        window.dialogManager.closeModal(modal);
    };

    window.submitChangeTier = async function() {
        const subId = document.getElementById('change-tier-sub-id').value;
        const newPlanId = document.getElementById('change-tier-plan-select').value;
        const errorEl = document.getElementById('modal-change-tier-error');
        const submitBtn = document.getElementById('btn-submit-change-tier');

        errorEl.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerText = 'Calculating Prorations...';

        try {
            await window.fetchApi(`/api/subscriptions/${subId}/change-tier`, {
                method: 'POST',
                body: JSON.stringify({ plan_id: newPlanId })
            });
            window.showToast('Tier upgraded with Stripe prorations', 'success');
            window.closeChangeTierModal();
            window.loadSubscriptions();
        } catch (err) {
            errorEl.innerText = err.message;
            errorEl.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Update Membership Tier';
        }
    };

    // -------------------------------------------------------------
    // 11. Immutable Audit Trail
    // -------------------------------------------------------------
    window.loadAuditLogs = async function() {
        const actor = document.getElementById('audit-actor-filter').value;
        let url = '/api/audit-logs';
        if (actor) url += `?actor=${encodeURIComponent(actor)}`;

        try {
            const res = await window.fetchApi(url);
            const logs = res.data || [];
            const tbody = document.getElementById('audit-tbody');
            tbody.innerHTML = '';

            if (!logs.length) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:32px; color:var(--p-color-text-subdued);">No audit records found.</td></tr>`;
                return;
            }

            logs.forEach(log => {
                const tr = document.createElement('tr');
                const encodedMeta = encodeURIComponent(JSON.stringify(log.metadata || {}, null, 2));

                tr.innerHTML = `
                    <td><span style="font-family:monospace; font-size:12px;">${new Date(log.created_at).toISOString().replace('T', ' ').substring(0, 19)}</span></td>
                    <td><strong>${log.action}</strong></td>
                    <td><span class="badge badge-info">${log.auditable_type ? log.auditable_type.split('\\').pop() : 'System'} #${log.auditable_id || '—'}</span></td>
                    <td>${log.actor_type || 'system'}</td>
                    <td>${log.ip_address || 'internal'}</td>
                    <td style="text-align: right;">
                        <button class="btn btn-secondary" style="min-height:26px; padding:2px 8px; font-size:11px;" onclick="window.openAuditJsonModal('${encodedMeta}')">JSON</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (err) {
            console.error('Error loading audit logs:', err);
        }
    };

    window.openAuditJsonModal = function(encodedData) {
        try {
            const json = decodeURIComponent(encodedData);
            document.getElementById('audit-json-content').innerText = json;
            window.dialogManager.openModal(document.getElementById('modal-audit-json'));
        } catch (e) {}
    };

    window.closeAuditJsonModal = function() {
        window.dialogManager.closeModal(document.getElementById('modal-audit-json'));
    };

    // -------------------------------------------------------------
    // 12. Stripe Connection Settings
    // -------------------------------------------------------------
    window.checkStripeStatus = async function() {
        try {
            const res = await window.fetchApi('/api/stripe/status');
            const statusBadge = document.getElementById('stripe-connection-status');
            const summaryCard = document.getElementById('stripe-connected-summary');
            const disconnectBtn = document.getElementById('btn-disconnect-stripe');

            if (res.connected) {
                statusBadge.className = 'badge badge-success';
                statusBadge.innerText = 'Connected & Active';
                summaryCard.style.display = 'block';
                disconnectBtn.style.display = 'inline-flex';
                document.getElementById('stripe-pub-masked').innerText = res.publishable_key || 'pk_live_...';
                document.getElementById('stripe-account-badge').innerText = res.stripe_account_id || 'Primary Account';
            } else {
                statusBadge.className = 'badge badge-warning';
                statusBadge.innerText = 'Not Connected';
                summaryCard.style.display = 'none';
                disconnectBtn.style.display = 'none';
            }
        } catch (err) {
            console.error('Error checking Stripe status:', err);
        }
    };

    window.connectStripe = async function() {
        const pubKey = document.getElementById('stripe-publishable-key').value;
        const secKey = document.getElementById('stripe-secret-key').value;
        const alertEl = document.getElementById('stripe-alert');
        const btn = document.getElementById('btn-connect-stripe');

        alertEl.style.display = 'none';
        btn.disabled = true;
        btn.innerText = 'Verifying with Stripe...';

        try {
            await window.fetchApi('/api/stripe/connect', {
                method: 'POST',
                body: JSON.stringify({ publishable_key: pubKey, secret_key: secKey })
            });
            window.showToast('Stripe credentials verified and encrypted', 'success');
            window.checkStripeStatus();
        } catch (err) {
            alertEl.style.display = 'block';
            alertEl.style.background = 'var(--nc-red-100)';
            alertEl.style.color = 'var(--nc-red-500)';
            alertEl.innerText = err.message;
        } finally {
            btn.disabled = false;
            btn.innerText = 'Save & Verify Credentials';
        }
    };

    window.disconnectStripe = async function() {
        if (!confirm('Are you sure you want to disconnect Stripe? Customers will not be able to pay deposits until reconnected.')) return;
        try {
            await window.fetchApi('/api/stripe/disconnect', { method: 'POST' });
            window.showToast('Stripe gateway disconnected', 'info');
            window.checkStripeStatus();
        } catch (err) {
            window.showToast('Error disconnecting: ' + err.message, 'error');
        }
    };

    // -------------------------------------------------------------
    // 13. CSV Export
    // -------------------------------------------------------------
    window.exportTransactionsCsv = async function() {
        window.showToast('Generating transaction ledger CSV...', 'info');
        try {
            const token = await getSessionToken();
            const url = `/api/transactions/export?shop=${encodeURIComponent(shopDomain)}&token=${encodeURIComponent(token)}`;
            window.open(url, '_blank');
        } catch (e) {
            window.showToast('Export failed: ' + e.message, 'error');
        }
    };

    // -------------------------------------------------------------
    // 14. Email Notification Templates Studio
    // -------------------------------------------------------------
    window.loadEmailTemplates = async function() {
        try {
            const res = await window.fetchApi('/api/email-templates');
            const templates = res.data || [];
            if (!templates.length) return;

            const tbody = document.getElementById('email-templates-tbody');
            tbody.innerHTML = '';

            templates.forEach(t => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${t.name || t.key}</strong><br><span style="color:var(--p-color-text-subdued); font-size:11px;">${t.key}</span></td>
                    <td>${t.subject}</td>
                    <td>${t.description || 'Customer notification'}</td>
                    <td><span class="badge ${t.is_active ? 'badge-success' : 'badge-neutral'}">${t.is_active ? 'Active' : 'Disabled'}</span></td>
                    <td style="text-align: right;">
                        <button class="btn btn-secondary" style="min-height:28px; padding:2px 10px;" onclick="window.previewEmailTemplate('${t.key}')">Preview &amp; Test</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) {
            console.error('Error loading email templates:', e);
        }
    };

    let activePreviewKey = null;

    window.previewEmailTemplate = async function(key) {
        activePreviewKey = key;
        const modal = document.getElementById('modal-email-preview');
        document.getElementById('email-preview-key-badge').innerText = key;
        document.getElementById('email-preview-subject').value = 'Loading preview...';
        document.getElementById('email-preview-html-body').innerHTML = '<div class="nc-shimmer" style="width:100%; height:200px;"></div>';

        window.dialogManager.openModal(modal);

        try {
            const res = await window.fetchApi(`/api/email-templates/${key}/preview`, { method: 'POST' });
            document.getElementById('email-preview-subject').value = res.subject || '';
            document.getElementById('email-preview-html-body').innerHTML = res.html || '<p>No preview content.</p>';
        } catch (err) {
            document.getElementById('email-preview-html-body').innerHTML = `<p style="color:var(--nc-red-500);">Error rendering preview: ${err.message}</p>`;
        }
    };

    window.closeEmailPreviewModal = function() {
        window.dialogManager.closeModal(document.getElementById('modal-email-preview'));
    };

    window.sendTestEmail = async function() {
        const recipient = document.getElementById('email-test-recipient').value;
        if (!recipient) {
            alert('Please enter an email address for the test send.');
            return;
        }

        const btn = document.getElementById('btn-send-test-email');
        btn.disabled = true;
        btn.innerText = 'Sending...';

        try {
            await window.fetchApi(`/api/email-templates/${activePreviewKey}/test-send`, {
                method: 'POST',
                body: JSON.stringify({ recipient_email: recipient })
            });
            window.showToast('Test email dispatched to ' + recipient, 'success');
        } catch (err) {
            window.showToast('Test send failed: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Send Test';
        }
    };

    // -------------------------------------------------------------
    // 15. DOM Initialization
    // -------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.replace('#', '');
        if (hash && ['overview', 'plans', 'orders', 'subscriptions', 'audit', 'settings'].includes(hash)) {
            window.navigateTab(hash);
        } else {
            window.navigateTab('overview');
        }
    });

})();
</script>
