import { icon } from '../components.js';

export function publicSurfaceNav(active) {
  const items = [['storefront', '/storefront', 'Storefront'], ['portal', '/portal/clara', 'Portal'], ['settlement', '/pay/balance/schedule-1001', 'Settlement'], ['card', '/pay/card/update/schedule-1001', 'Card update'], ['demo', '/demo', 'Studio lab']];
  return `<nav class="public-route-nav" aria-label="Prototype pages">${items.map(([key, href, label]) => `<a href="${href}" data-nav="${href}" class="${active === key ? 'public-route-active' : ''}">${label}</a>`).join('')}</nav>`;
}
