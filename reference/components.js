export const esc = (value = '') => String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
export const money = cents => `$${(Number(cents || 0) / 100).toLocaleString('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} AUD`;
export const shortMoney = cents => `$${(Number(cents || 0) / 100).toLocaleString('en-AU', { maximumFractionDigits: 0 })}`;
export const badge = (text, cls = 'status-clay') => `<span class="status-pill ${cls}"><span class="status-dot" aria-hidden="true"></span>${esc(text)}</span>`;
export const button = (text, action, variant = 'secondary', extra = '') => `<button type="button" class="button button-${variant}" data-action="${esc(action)}" ${extra}>${text}</button>`;
export const buttonLink = (text, path, variant = 'secondary', iconHtml = '') => `<a class="button button-${variant}" href="${esc(path)}" data-nav="${esc(path)}">${iconHtml}${text}</a>`;
export const brandMark = (small = false) => `<span class="brand-lockup ${small ? 'brand-lockup-small' : ''}"><span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 40 40" fill="none"><path d="M8 32V20C8 12.82 13.82 7 21 7c6.08 0 11 4.92 11 11v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M13 32V20.5c0-4.14 3.36-7.5 7.5-7.5S28 16.36 28 20.5V32" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M6 32h27" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="20.5" cy="22" r="2" fill="currentColor"/></svg></span><span class="brand-wordmark"><strong>northwind</strong><small>ceramics · studio payments</small></span></span>`;

const iconPaths = {
  grid: '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="15" y="4" width="5" height="7" rx="1.5"/><rect x="4" y="15" width="7" height="5" rx="1.5"/><rect x="15" y="15" width="5" height="5" rx="1.5"/>',
  plans: '<path d="M5 6.5h14M5 12h14M5 17.5h9"/><circle cx="17.5" cy="17.5" r="2.5"/>',
  receipt: '<path d="M6 3.5h11.5L21 7v13.5l-3-1.8-3 1.8-3-1.8-3 1.8-3-1.8z"/><path d="M9 10h6M9 14h7"/>',
  people: '<circle cx="9" cy="8" r="3"/><path d="M3.5 19c.4-3 2.3-4.5 5.5-4.5s5.1 1.5 5.5 4.5M16 5.5a3 3 0 0 1 0 5.5M17 14.5c2.2.4 3.5 1.8 4 4"/>',
  history: '<path d="M4 11a8 8 0 1 1 .6 4.3M4 5v6h6"/><path d="M12 7.5v5l3.5 2"/>',
  settings: '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.4-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.4-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a7 7 0 0 1-.1 1.7Z" transform="translate(-1.8 -1.5) scale(1.15)"/>',
  arrow: '<path d="M4 12h15M13 6l6 6-6 6"/>',
  chevron: '<path d="m7 10 5 5 5-5"/>',
  plus: '<path d="M12 5v14M5 12h14"/>',
  search: '<circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4 4"/>',
  download: '<path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/>',
  refresh: '<path d="M19 7v5h-5M5 17v-5h5"/><path d="M6.5 9a6.5 6.5 0 0 1 11-2L19 8M5 16l1.5 1a6.5 6.5 0 0 0 11-2"/>',
  eye: '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
  copy: '<rect x="8" y="8" width="11" height="12" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h2"/>',
  close: '<path d="m6 6 12 12M18 6 6 18"/>',
  lock: '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>',
  spark: '<path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="m19 16 .8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8L19 16Z"/>',
  kiln: '<path d="M5 20V11a7 7 0 0 1 14 0v9M3 20h18M8 20v-4a4 4 0 0 1 8 0v4"/><path d="M9 8c1-1 1-2 0-3m6 3c1-1 1-2 0-3"/>',
  box: '<path d="m12 3 9 5-9 5-9-5 9-5Zm-9 5v9l9 5 9-5V8M12 13v9"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  check: '<path d="m5 12 4 4L19 6"/>',
  mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
  shield: '<path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.3 2.3 4.7-5"/>',
  bag: '<path d="M5 8h14l1 12H4L5 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/>'
};
export const icon = (name, cls = '') => `<svg class="icon ${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${iconPaths[name] || iconPaths.spark}</svg>`;
export const initials = name => String(name).split(/\s+/).map(part => part[0]).slice(0, 2).join('').toUpperCase();
export const sectionHeading = (eyebrow, title, copy = '') => `<div class="section-heading"><div><p class="eyebrow">${esc(eyebrow)}</p><h1>${title}</h1>${copy ? `<p class="section-copy">${copy}</p>` : ''}</div></div>`;
export const emptyState = (title, copy, action = '') => `<div class="empty-state"><span class="empty-icon">${icon('kiln')}</span><h3>${esc(title)}</h3><p>${esc(copy)}</p>${action}</div>`;
