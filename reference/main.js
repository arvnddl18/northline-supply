import { renderAdmin, drawerContent, refundModalMarkup, changeTierModalMarkup, auditJsonMarkup, emailPreviewMarkup } from './views/admin.js';
import { renderStorefront, checkoutModalMarkup } from './views/storefront.js';
import { renderPortal, renderMagicLinkRequest, portalModalMarkup } from './views/portal.js';
import { renderBalance, renderCardUpdate } from './views/settlement.js';
import { renderDemo } from './views/demo.js';
import { publicSurfaceNav } from './views/shared.js';
import { initialPlans, initialOrders, initialSubscriptions, initialAudit, emailTemplates } from './data.js';
import { esc, money, icon, brandMark } from './components.js';

const app = document.querySelector('#app');
const overlayRoot = document.querySelector('#overlay-root');
const toastStack = document.querySelector('#toast-stack');
let searchTimer;
let cardFieldTimer;
let toastSequence = 0;

function expandedOrders() {
  return [...initialOrders, ...Array.from({ length: 34 }, (_, i) => {
    const source = initialOrders[i % initialOrders.length];
    const id = String(1009 + i);
    const days = 5 + (i % 24);
    return { ...source, id, date: `${String(Math.max(1, 12 - (i % 11))).padStart(2, '0')} Oct 2026`, due: source.balance ? `${String(days).padStart(2, '0')} Oct 2026` : '—' };
  })];
}

const state = {
  activeTab: location.hash.replace('#', '') || 'overview',
  role: 'owner', gatewayConnected: true,
  plans: initialPlans.map(p => ({ ...p })), orders: expandedOrders(), subscriptions: initialSubscriptions.map(s => ({ ...s })), audit: initialAudit.map(e => ({ ...e })),
  orderFilter: 'All Orders', orderSearch: '', pageNum: 1, auditActor: 'All Actors',
  storeChoice: 'deposit', demoChoice: 'deposit', settlementState: 'active', cardState: 'active',
  lastAdminHash: location.hash.replace('#', '') || 'overview',
  cardFieldRoute: '', cardFieldReady: false
};

const surfaceNames = { '/storefront': 'Storefront', '/portal/expired': 'Expired portal link', '/demo': 'Studio lab' };

function publicFrame(page, active) {
  return `<header class="public-topbar"><a href="/" data-nav="/" class="public-topbar-brand">${brandMark(true)}</a>${publicSurfaceNav(active)}<a href="/#overview" data-nav="#overview" class="public-admin-link">${icon('grid')}<span>Studio admin</span></a></header><div class="public-topbar-spacer"></div>${page}`;
}

function routePage() {
  const path = location.pathname.replace(/\/$/, '') || '/';
  if (path.startsWith('/pay/card/update/')) {
    if (state.cardFieldRoute !== path) {
      clearTimeout(cardFieldTimer);
      state.cardFieldRoute = path;
      state.cardFieldReady = false;
      cardFieldTimer = window.setTimeout(() => {
        state.cardFieldReady = true;
        if (location.pathname.replace(/\/$/, '') === path) render({ preserveFocus: false });
      }, 900);
    }
  } else if (state.cardFieldRoute) {
    clearTimeout(cardFieldTimer);
    state.cardFieldRoute = '';
    state.cardFieldReady = false;
  }
  if (path === '/') {
    state.activeTab = location.hash.replace('#', '') || state.lastAdminHash || 'overview';
    if (location.hash) state.lastAdminHash = state.activeTab;
    document.title = `${state.activeTab.replace(/\b\w/g, c => c.toUpperCase())} · Northwind Ceramics`;
    return renderAdmin(state);
  }
  if (path === '/storefront') { document.title = 'Speckled stoneware vase · Northwind Ceramics'; return publicFrame(renderStorefront(state), 'storefront'); }
  if (path === '/portal/request-link') { document.title = 'Request a studio access link · Northwind Ceramics'; return publicFrame(renderMagicLinkRequest(), 'portal'); }
  if (path === '/portal/expired') { document.title = 'Secure access link · Northwind Ceramics'; return publicFrame(renderPortal(state), 'portal'); }
  if (path.startsWith('/portal/')) { document.title = 'Your studio account · Northwind Ceramics'; return publicFrame(renderPortal(state), 'portal'); }
  if (path.startsWith('/pay/balance/')) { document.title = 'Balance settlement · Northwind Ceramics'; return publicFrame(renderBalance(state), 'settlement'); }
  if (path.startsWith('/pay/card/update/')) { document.title = 'Update payment method · Northwind Ceramics'; return publicFrame(renderCardUpdate(state), 'card'); }
  if (path === '/demo') { document.title = 'Studio lab · Northwind Ceramics'; return publicFrame(renderDemo(state), 'demo'); }
  document.title = 'Northwind Ceramics';
  return publicFrame(renderDemo(state), 'demo');
}

function render({ preserveFocus = true } = {}) {
  const active = document.activeElement;
  const focusKey = preserveFocus && active && app.contains(active) ? active.getAttribute('data-focus-key') : null;
  const start = focusKey && typeof active.selectionStart === 'number' ? active.selectionStart : null;
  const end = focusKey && typeof active.selectionEnd === 'number' ? active.selectionEnd : null;
  app.innerHTML = routePage();
  if (focusKey) {
    const next = app.querySelector(`[data-focus-key="${CSS.escape(focusKey)}"]`);
    if (next) { next.focus({ preventScroll: true }); if (start !== null && next.setSelectionRange) next.setSelectionRange(start, end); }
  }
}

function navigate(path) {
  if (path.startsWith('#')) {
    if (location.pathname !== '/') {
      history.pushState({}, '', `/${path}`);
    } else if (location.hash !== path) {
      history.pushState({}, '', `/${path}`);
    }
    state.activeTab = path.slice(1) || 'overview';
    state.lastAdminHash = state.activeTab;
    render();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  if (path === '/') state.activeTab = state.lastAdminHash || 'overview';
  history.pushState({}, '', path);
  closeDialog();
  render();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

let dialogFocusReturn = null;

function focusSelectorFor(element) {
  const action = element?.closest?.('[data-action]');
  if (action) {
    let selector = `[data-action="${CSS.escape(action.dataset.action)}"]`;
    for (const key of ['id', 'index', 'key', 'filter', 'state']) {
      if (action.dataset[key] !== undefined) selector += `[data-${key}="${CSS.escape(action.dataset[key])}"]`;
    }
    return selector;
  }
  const focusKey = element?.closest?.('[data-focus-key]')?.getAttribute('data-focus-key');
  if (focusKey) return `[data-focus-key="${CSS.escape(focusKey)}"]`;
  const nav = element?.closest?.('[data-nav]')?.getAttribute('data-nav');
  if (nav !== null && nav !== undefined) return `[data-nav="${CSS.escape(nav)}"]`;
  return element?.id ? `#${CSS.escape(element.id)}` : '';
}

function openDialog({ title, kicker = '', body = '', kind = 'modal', wide = false }) {
  const opener = document.activeElement;
  dialogFocusReturn = { element: opener, selector: focusSelectorFor(opener) };
  const current = overlayRoot.querySelector('dialog');
  if (current) { if (current.open) current.close(); overlayRoot.replaceChildren(); }
  app.inert = true;
  overlayRoot.innerHTML = `<dialog class="app-dialog ${kind === 'drawer' ? 'app-drawer' : ''} ${wide ? 'app-dialog-wide' : ''}" role="dialog" aria-modal="true" aria-labelledby="dialog-title"><div class="dialog-head"><div>${kicker ? `<p class="eyebrow">${esc(kicker)}</p>` : ''}<h2 id="dialog-title">${title}</h2></div><button type="button" class="icon-button dialog-x" data-action="close-dialog" aria-label="Close dialog">${icon('close')}</button></div><div class="dialog-body">${body}</div></dialog>`;
  const dialog = overlayRoot.querySelector('dialog');
  window.dialogManager = { dialog, closeTopmost: closeDialog, close: closeDialog };
  dialog.addEventListener('click', event => { if (event.target === dialog) closeDialog(); });
  dialog.addEventListener('cancel', event => { event.preventDefault(); closeDialog(); });
  dialog.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const focusable = [...dialog.querySelectorAll('a[href], button:not(:disabled), input:not(:disabled), select:not(:disabled), textarea:not(:disabled), [tabindex]:not([tabindex="-1"])')].filter(node => node.getClientRects().length && !node.hidden);
    if (!focusable.length) { event.preventDefault(); dialog.focus(); return; }
    const first = focusable[0], last = focusable.at(-1);
    if (event.shiftKey && (document.activeElement === first || !dialog.contains(document.activeElement))) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && (document.activeElement === last || !dialog.contains(document.activeElement))) { event.preventDefault(); first.focus(); }
  });
  dialog.addEventListener('close', () => {
    if (window.dialogManager?.dialog !== dialog) return;
    overlayRoot.replaceChildren();
    window.dialogManager = null;
    app.inert = false;
    const restore = dialogFocusReturn;
    dialogFocusReturn = null;
    queueMicrotask(() => {
      const target = (restore?.element?.isConnected && restore.element) || (restore?.selector && app.querySelector(restore.selector)) || app.querySelector('#main-content h1') || app;
      if (target === app && !app.hasAttribute('tabindex')) app.tabIndex = -1;
      target?.focus?.({ preventScroll: true });
    });
  });
  dialog.showModal();
  requestAnimationFrame(() => dialog.querySelector('input:not([type="hidden"]):not(:disabled), select:not(:disabled), textarea:not(:disabled), button:not(.dialog-x):not(:disabled), a[href]')?.focus({ preventScroll: true }) || dialog.querySelector('.dialog-x')?.focus({ preventScroll: true }));
}

function closeDialog() {
  const dialog = overlayRoot.querySelector('dialog'); if (!dialog) return;
  if (dialog.dataset.closing) return;
  if (dialog.open) {
    dialog.dataset.closing = 'true';
    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    if (reduced) { dialog.close(); return; }
    dialog.classList.add('dialog-closing');
    window.setTimeout(() => { if (dialog.open) dialog.close(); }, dialog.classList.contains('app-drawer') ? 210 : 170);
  } else {
    overlayRoot.replaceChildren(); app.inert = false; window.dialogManager = null;
  }
}

function toast(message, kind = 'success') {
  const id = `toast-${++toastSequence}`;
  const symbol = kind === 'error' ? 'close' : kind === 'warning' ? 'clock' : 'check';
  const node = document.createElement('div');
  node.id = id;
  node.className = `toast toast-${kind}`;
  node.setAttribute('role', kind === 'error' ? 'alert' : 'status');
  node.innerHTML = `<span class="toast-mark">${icon(symbol)}</span><span class="toast-message">${esc(message)}</span><button type="button" class="toast-close" aria-label="Dismiss notification">${icon('close')}</button><i class="toast-timer"></i>`;
  toastStack.append(node);
  let remaining = 4000;
  let started = Date.now();
  let timer = setTimeout(remove, remaining);
  function remove() { clearTimeout(timer); node.classList.add('toast-leave'); setTimeout(() => node.remove(), 260); }
  node.addEventListener('mouseenter', () => { clearTimeout(timer); remaining -= Date.now() - started; node.classList.add('toast-paused'); });
  node.addEventListener('mouseleave', () => { started = Date.now(); node.classList.remove('toast-paused'); timer = setTimeout(remove, Math.max(350, remaining)); });
  node.querySelector('.toast-close').addEventListener('click', remove);
}

async function copyText(text, message) {
  try { await navigator.clipboard.writeText(text); }
  catch {
    const input = document.createElement('textarea'); input.value = text; input.style.position = 'fixed'; input.style.opacity = '0'; document.body.append(input); input.select(); document.execCommand('copy'); input.remove();
  }
  toast(message);
}

function findOrder(id) { return state.orders.find(o => o.id === String(id)) || state.orders[0]; }
function findSubscription(id) { return state.subscriptions.find(s => s.id === String(id)) || state.subscriptions[0]; }

function openPlan(id = '') {
  const modal = drawerContent(state, 'plan', id);
  openDialog({ title: modal.title, kicker: modal.kicker, body: modal.content, kind: 'drawer' });
  requestAnimationFrame(updatePlanCalc);
}
function openOrder(id) {
  const modal = drawerContent(state, 'order', id);
  openDialog({ title: modal.title, kicker: modal.kicker, body: modal.content, kind: 'drawer' });
}
function openRefund(id) {
  if (state.role !== 'owner') { toast('Refunds are restricted to the Studio Owner.', 'warning'); return; }
  const order = findOrder(id);
  openDialog({ title: 'A careful return.', kicker: 'SIMULATED REFUND', body: refundModalMarkup(order) });
}
function openDeletePlan(id) {
  const plan = state.plans.find(p => p.id === String(id)); if (!plan) return;
  openDialog({ title: 'Remove this plan?', kicker: 'CONFIRM CHANGE', body: `<p class="dialog-copy">“${esc(plan.name)}” will be removed from this local preview. Existing orders and their schedules remain unchanged.</p><form data-form="delete-plan"><input type="hidden" name="id" value="${esc(plan.id)}"><div class="dialog-footer"><button type="button" class="button button-secondary" data-action="close-dialog">Keep this plan</button><button type="submit" class="button button-danger">Remove plan</button></div></form>` });
}
function openSubAction(kind, id) {
  const sub = findSubscription(id);
  const title = kind === 'pause' ? 'Pause a little while' : kind === 'skip' ? 'Skip the next firing?' : 'End this membership?';
  const note = kind === 'pause' ? 'This member will keep their place; billing and delivery will pause for one month.' : kind === 'skip' ? 'The upcoming renewal will be skipped and billing resumes the following month.' : 'This ends the local demo membership at period end. Existing orders stay in the ledger.';
  const formKey = `${kind}-sub-confirm`;
  openDialog({ title, kicker: `${esc(sub.name)} · ${esc(sub.plan)}`, body: `<div class="notice-soft ${kind === 'cancel' ? 'notice-warning' : ''}">${icon(kind === 'cancel' ? 'box' : 'clock')}<span>${note}${kind === 'cancel' ? '<br><strong>Owner-only simulated action.</strong>' : ''}</span></div><form data-form="${formKey}"><input type="hidden" name="id" value="${esc(sub.id)}"><div class="dialog-footer"><button type="button" class="button button-secondary" data-action="close-dialog">Go back</button><button type="submit" class="button ${kind === 'cancel' ? 'button-danger' : 'button-primary'}">${kind === 'pause' ? 'Pause membership' : kind === 'skip' ? 'Skip this cycle' : 'Confirm cancellation'}</button></div></form>` });
}
function openTier(id) {
  const sub = findSubscription(id);
  if (state.role !== 'owner') { toast('Membership changes are restricted to the Studio Owner.', 'warning'); return; }
  openDialog({ title: 'A new rhythm for the studio.', kicker: 'SWITCH MEMBERSHIP TIER', body: changeTierModalMarkup(sub) });
}
function openAudit(index) {
  const event = state.audit[Number(index)]; if (!event) return;
  openDialog({ title: 'A closer look at this record.', kicker: 'AUDIT EVENT · READ ONLY', body: auditJsonMarkup(event), wide: true });
}
function openEmail(key) {
  const template = emailTemplates.find(t => t.key === key) || emailTemplates[0];
  openDialog({ title: 'A note from the studio.', kicker: 'EMAIL TEMPLATE PREVIEW', body: emailPreviewMarkup(template), wide: true });
}

function csvExport() {
  const headers = ['Order', 'Customer', 'Email', 'Status', 'Total AUD cents', 'Deposit AUD cents', 'Balance AUD cents', 'Order date'];
  const values = state.orders.map(o => [o.id, o.name, o.email, o.status, o.total, o.deposit, o.balance, o.date]);
  const csv = [headers, ...values].map(row => row.map(value => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
  const href = URL.createObjectURL(blob);
  const anchor = document.createElement('a'); anchor.href = href; anchor.download = 'northwind-studio-ledger-demo.csv'; document.body.append(anchor); anchor.click(); anchor.remove(); URL.revokeObjectURL(href);
  toast('Sample ledger exported as a CSV preview.');
}

function act(element) {
  const action = element.dataset.action;
  const id = element.dataset.id;
  if (!action) return;
  switch (action) {
    case 'close-dialog': closeDialog(); break;
    case 'new-plan': openPlan(); break;
    case 'edit-plan': openPlan(id); break;
    case 'delete-plan': openDeletePlan(id); break;
    case 'inspect-order': openOrder(id); break;
    case 'refund-order': openRefund(id); break;
    case 'view-audit': openAudit(element.dataset.index); break;
    case 'copy-link': copyText(`${location.origin}/pay/balance/schedule-${id}?signed=demo`, `Demo payment link for order #${id} copied.`); break;
    case 'copy-email': copyText(element.dataset.email || '', 'Customer email copied.'); break;
    case 'copy-command': copyText(element.dataset.command || '', 'Command copied to clipboard.'); break;
    case 'copy-json': copyText(overlayRoot.querySelector('.json-viewer')?.innerText || '', 'Audit metadata copied.'); break;
    case 'export-csv': csvExport(); break;
    case 'filter-orders': state.orderFilter = element.dataset.filter; state.pageNum = 1; render(); break;
    case 'page-prev': state.pageNum = Math.max(1, state.pageNum - 1); render(); break;
    case 'page-next': state.pageNum = Math.min(5, state.pageNum + 1); render(); break;
    case 'refresh-orders': toast('The demo ledger is up to date.'); break;
    case 'refresh-audit': toast('The sample audit trail is current.'); break;
    case 'notifications': toast('You’re all caught up with the studio.', 'success'); break;
    case 'plans-active': toast(`${state.plans.filter(p => p.active).length} active plans are available at the storefront.`); break;
    case 'pause-sub': openSubAction('pause', id); break;
    case 'skip-sub': openSubAction('skip', id); break;
    case 'cancel-sub': if (state.role !== 'owner') toast('Membership cancellation is restricted to the Studio Owner.', 'warning'); else openSubAction('cancel', id); break;
    case 'resume-sub': { const sub = findSubscription(id); sub.status = 'Active'; render(); toast(`${sub.name}’s Ceramic Club deliveries resumed.`); break; }
    case 'change-tier': openTier(id); break;
    case 'preview-email': openEmail(element.dataset.key); break;
    case 'simulate-connect': state.gatewayConnected = true; render(); toast('Demo gateway verified. No live connection was made.'); break;
    case 'disconnect-gateway': openDialog({ title: 'Disconnect the demo gateway?', kicker: 'SIMULATED SETTING', body: `<p class="dialog-copy">This only changes the sample workspace. No Stripe account is connected.</p><form data-form="disconnect-gateway"><div class="dialog-footer"><button class="button button-secondary" type="button" data-action="close-dialog">Keep connected</button><button class="button button-danger" type="submit">Disconnect demo</button></div></form>` }); break;
    case 'pause-portal': openDialog({ title: 'Make room for a little pause.', kicker: 'CERAMIC CLUB · YOUR CHOICE', body: portalModalMarkup('pause') }); break;
    case 'skip-portal': openDialog({ title: 'One cycle, set aside.', kicker: 'CERAMIC CLUB · YOUR CHOICE', body: portalModalMarkup('skip') }); break;
    case 'tier-portal': openDialog({ title: 'Find your studio rhythm.', kicker: 'CERAMIC CLUB · YOUR CHOICE', body: portalModalMarkup('tier') }); break;
    case 'cancel-portal': openDialog({ title: 'Before you leave the club.', kicker: 'CERAMIC CLUB · CONFIRMATION', body: portalModalMarkup('cancel') }); break;
    case 'resume-portal': { const sub = state.subscriptions[0]; sub.status = 'Active'; render(); toast('Your Ceramic Club deliveries are back in the rhythm.'); break; }
    case 'portal-link-expired': navigate('/portal/request-link'); break;
    case 'set-balance-state': state.settlementState = element.dataset.state; render(); break;
    case 'set-card-state': state.cardState = element.dataset.state; render(); break;
    case 'pay-balance': openDialog({ title: 'Ready for the last step?', kicker: 'SECURE CHECKOUT PREVIEW', body: `<div class="checkout-preview"><span class="checkout-icon">${icon('lock')}</span><h2>${money(14000)}</h2><p>Final balance for order #1001 · Speckled stoneware vase.</p><div class="notice-soft">${icon('shield')} <span>Demo only — no payment or card details will be collected.</span></div><div class="dialog-footer"><button type="button" class="button button-secondary" data-action="close-dialog">Not just yet</button><button type="button" class="button button-primary" data-action="settle-paid">Complete demo settlement ${icon('arrow')}</button></div></div>` }); break;
    case 'settle-paid': state.settlementState = 'paid'; state.orders[0].status = 'Paid in Full'; state.orders[0].balance = 0; closeDialog(); render(); toast('Balance settled in this demo. The piece is ready for careful packing.'); break;
    case 'simulate-card-update': state.cardState = 'success'; render(); toast('Demo card update complete — no card details were collected.'); break;
    case 'zoom-product': openDialog({ title: 'A closer look at the studio glaze.', kicker: 'AUTUMN FIRING · NO. 08', wide: true, body: `<div class="zoom-image-card"><img src="/manus-storage/vase_e2a3cd04.webp" alt="A closer view of the hand-thrown speckled stoneware vase"><p>Each vase is finished by hand, so small variations in shape and glaze make every piece its own.</p></div>` }); break;
    case 'simulate-checkout-complete': closeDialog(); toast('Checkout handoff simulated. No payment was collected.'); break;
    case 'simulate-demo-checkout': openDialog({ title: 'A checkout handoff, in miniature.', kicker: 'STOREFRONT SIMULATOR', body: `<div class="checkout-preview"><span class="checkout-icon">${icon('lock')}</span><p class="eyebrow">SPECKLED STONEWARE VASE · DEMO</p><h2>${money(state.demoChoice === 'full' ? 15000 : 4500)}</h2><p>${state.demoChoice === 'full' ? 'Full payment today' : '30% pre-order deposit · $105.00 AUD before dispatch'}.</p><div class="notice-soft">${icon('shield')} <span>App Proxy handoff preview. No card details or payment are collected.</span></div><div class="dialog-footer"><button type="button" class="button button-secondary" data-action="close-dialog">Return to simulator</button><button type="button" class="button button-primary" data-action="simulate-checkout-complete">Confirm demo handoff ${icon('arrow')}</button></div></div>` }); break;
    default: break;
  }
}

function handleForm(form) {
  const kind = form.dataset.form;
  const values = Object.fromEntries(new FormData(form).entries());
  if (kind === 'plan-save') {
    const editing = values.id && state.plans.find(p => p.id === values.id);
    const isInstall = values.kind === 'installment';
    const terms = isInstall ? `${values.installments || 3} splits · every ${values.frequency === '1_week' ? 'week' : values.frequency === '1_month' ? 'month' : '2 weeks'}` : values.depositType === 'fixed' ? `$${(Number(values.depositValue) / 100).toFixed(2)} AUD upfront · balance before dispatch` : `${Number(values.depositValue) || 30}% upfront · balance before dispatch`;
    const plan = { id: editing?.id || `pl_0${state.plans.length + 1}`, name: values.name.trim(), type: isInstall ? 'Installment plan' : 'Deposit plan', terms, scope: values.products || values.collections ? 'Specific products' : 'Shop-wide default', products: values.products, collections: values.collections, depositPct: Number(values.depositValue) || 30, active: form.elements.active.checked };
    if (editing) Object.assign(editing, plan); else state.plans.unshift(plan);
    closeDialog(); render(); toast(editing ? 'Payment plan updated in the demo.' : 'Your new payment plan is ready in the demo.'); return;
  }
  if (kind === 'refund-submit') {
    const order = findOrder(values.id); const amount = Number(values.amount);
    if (!Number.isInteger(amount) || amount < 1 || amount > order.deposit) { toast('Enter a valid amount within the captured deposit.', 'error'); return; }
    if (amount >= order.deposit) { order.status = 'Refunded'; order.balance = 0; }
    state.audit.unshift({ time: new Date().toISOString().replace('T', ' ').slice(0, 19), action: 'order.refund_simulated', target: `Order #${order.id}`, actor: state.role === 'owner' ? 'Emma Carter' : 'Studio Assistant', origin: 'Local demo', kind: 'Emma Carter', meta: { amount, reason: values.reason, simulation: true } });
    closeDialog(); render(); toast(`Refund of ${money(amount)} simulated for order #${order.id}.`); return;
  }
  if (kind === 'delete-plan') { state.plans = state.plans.filter(p => p.id !== values.id); closeDialog(); render(); toast('Plan removed from this demo.'); return; }
  if (kind === 'tier-submit') { const sub = findSubscription(values.id); sub.plan = values.tier.split(' · ')[0]; const price = values.tier.match(/\$(\d+\.\d{2})/); if (price) sub.price = Math.round(Number(price[1]) * 100); closeDialog(); render(); toast(`${sub.name}’s plan changed in this preview.`); return; }
  if (kind === 'email-test') { const recipient = form.querySelector('[name="recipient"]')?.value || 'studio recipient'; closeDialog(); toast(`Test email preview queued for ${recipient} — no message was sent.`); return; }
  if (kind === 'portal-lifecycle') {
    const action = values.action; const sub = state.subscriptions[0];
    if (action === 'pause') sub.status = 'Paused';
    if (action === 'cancel') sub.status = 'Cancelled';
    if (action === 'tier') { sub.plan = values.tier.split(' · ')[0]; const price = values.tier.match(/\$(\d+\.\d{2})/); if (price) sub.price = Math.round(Number(price[1]) * 100); }
    if (action === 'skip') sub.lastSkipped = true;
    closeDialog(); render(); toast(action === 'pause' ? `Membership paused for ${values.months || 1} month${values.months === '2' ? 's' : ''}.` : action === 'skip' ? 'Your next Ceramic Club cycle has been skipped.' : action === 'cancel' ? 'Your membership was cancelled in this preview.' : 'Your new Ceramic Club tier is set for the demo.'); return;
  }
  if (kind === 'pause-sub-confirm' || kind === 'skip-sub-confirm' || kind === 'cancel-sub-confirm') {
    const sub = findSubscription(values.id); const action = kind.split('-')[0];
    if (action === 'pause') sub.status = 'Paused';
    if (action === 'cancel') { if (state.role !== 'owner') { closeDialog(); toast('Cancellation is restricted to the Studio Owner.', 'warning'); return; } sub.status = 'Cancelled'; }
    if (action === 'skip') sub.lastSkipped = true;
    closeDialog(); render(); toast(action === 'pause' ? `${sub.name}’s deliveries are paused in this demo.` : action === 'skip' ? `${sub.name}’s next cycle is skipped in this demo.` : `${sub.name}’s membership is cancelled in this demo.`); return;
  }
  if (kind === 'disconnect-gateway') { state.gatewayConnected = false; closeDialog(); render(); toast('Demo gateway disconnected.'); return; }
  if (kind === 'magic-link') { openDialog({ title: 'Request received.', kicker: 'DEMO · NO EMAIL SENT', body: `<div class="magic-sent-state">${icon('mail')}<p>If that address matches a Northwind account, a fresh access link would be sent in a live setup. This prototype never sends email, and the confirmation is the same either way.</p><button type="button" class="button button-primary" data-action="close-dialog">Back to Northwind</button></div>` }); return; }
  if (kind === 'storefront-checkout') { openDialog({ title: 'Your piece, your way.', kicker: 'CHECKOUT PREVIEW', body: checkoutModalMarkup(state.storeChoice) }); return; }
}

app.addEventListener('click', event => {
  const link = event.target.closest('[data-nav]');
  if (link) { event.preventDefault(); navigate(link.dataset.nav); return; }
  const actionable = event.target.closest('[data-action]');
  if (actionable) { event.preventDefault(); act(actionable); }
});

app.addEventListener('submit', event => {
  const form = event.target.closest('[data-form]'); if (!form) return;
  event.preventDefault(); handleForm(form);
});

document.addEventListener('click', event => {
  const actionable = event.target.closest('#overlay-root [data-action]');
  if (actionable) { event.preventDefault(); act(actionable); }
});

document.addEventListener('submit', event => {
  const form = event.target.closest('#overlay-root [data-form]'); if (!form) return;
  event.preventDefault(); handleForm(form);
});

document.addEventListener('change', event => {
  const target = event.target;
  if (target.id === 'role-select') { state.role = target.value; render(); toast(`Viewing as ${target.value === 'owner' ? 'Studio Owner' : 'Studio Staff'}.`); }
  if (target.id === 'audit-actor') { state.auditActor = target.value; render(); }
  if (target.matches('[data-action="toggle-plan"]')) { const plan = state.plans.find(p => p.id === target.dataset.id); if (plan) { plan.active = target.checked; toast(`${plan.name} ${plan.active ? 'is active' : 'is paused'} in this preview.`); render(); } }
  if (target.name === 'checkout-plan') { state.storeChoice = target.value; render(); }
  if (target.name === 'demo-choice') { state.demoChoice = target.value; render(); }
  if (target.id === 'plan-kind') { document.querySelector('.plan-deposit-fields')?.classList.toggle('is-hidden', target.value !== 'deposit'); document.querySelector('.plan-install-fields')?.classList.toggle('is-hidden', target.value !== 'installment'); updatePlanCalc(); }
  if (target.name === 'depositType' || target.name === 'depositValue') updatePlanCalc();
  if (target.name === 'installments' || target.name === 'frequency') updatePlanCalc();
});

document.addEventListener('input', event => {
  const target = event.target;
  if (target.matches('[data-focus-key="order-search"]')) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { state.orderSearch = target.value; state.pageNum = 1; render(); }, 300);
  }
  if (target.name === 'depositValue') updatePlanCalc();
});

function updatePlanCalc() {
  const kind = document.querySelector('#plan-kind')?.value || 'deposit';
  const type = document.querySelector('[name="depositType"]')?.value || 'percentage';
  const value = Number(document.querySelector('[name="depositValue"]')?.value || 30);
  const dueNode = document.querySelector('[data-calc-deposit]');
  const balanceNode = document.querySelector('[data-calc-balance]');
  if (!dueNode || !balanceNode) return;
  const total = 20000;
  const dueLabel = document.querySelector('[data-calc-due-label]');
  const balanceLabel = document.querySelector('[data-calc-balance-label]');
  const scheduleNode = document.querySelector('[data-calc-installment-rows]');
  let due;
  if (kind === 'installment') {
    const count = Math.max(2, Math.min(4, Number(document.querySelector('[name="installments"]')?.value) || 3));
    const base = Math.floor(total / count), remainder = total % count;
    const amounts = Array.from({ length: count }, (_, i) => base + (i < remainder ? 1 : 0));
    const cadence = document.querySelector('[name="frequency"]')?.value || '2_week';
    const rows = amounts.map((amount, i) => {
      const when = i === 0 ? 'Today' : cadence === '1_month' ? `In ${i} month${i > 1 ? 's' : ''}` : `${cadence === '2_week' ? i * 2 : i} week${(cadence === '2_week' ? i * 2 : i) > 1 ? 's' : ''}`;
      return `<div class="calc-installment-row"><span>Payment ${i + 1}<small>${when}</small></span><strong>$${(amount / 100).toFixed(2)}</strong></div>`;
    }).join('');
    due = amounts[0];
    if (dueLabel) dueLabel.textContent = `Due today · 1 of ${count}`;
    if (balanceLabel) balanceLabel.textContent = `Remaining · ${count - 1} payments`;
    if (scheduleNode) { scheduleNode.innerHTML = rows; scheduleNode.classList.remove('is-hidden'); }
  } else {
    due = type === 'fixed' ? Math.min(total, Math.max(0, Math.round(value))) : Math.round(total * Math.min(100, Math.max(0, value)) / 100);
    if (dueLabel) dueLabel.textContent = 'Due today';
    if (balanceLabel) balanceLabel.textContent = 'Before dispatch';
    scheduleNode?.classList.add('is-hidden');
  }
  dueNode.textContent = `$${(due / 100).toFixed(2)}`;
  balanceNode.textContent = `$${((total - due) / 100).toFixed(2)}`;
}

window.addEventListener('popstate', () => { closeDialog(); render(); });
window.addEventListener('hashchange', () => { if (location.pathname === '/') { state.activeTab = location.hash.slice(1) || 'overview'; state.lastAdminHash = state.activeTab; render(); } });
window.addEventListener('keydown', event => { if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); navigate('/#orders'); requestAnimationFrame(() => app.querySelector('[data-focus-key="order-search"]')?.focus()); } });

render({ preserveFocus: false });
