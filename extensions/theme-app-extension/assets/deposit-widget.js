/**
 * Northwind Ceramics - Storefront Deposit Widget (Vanilla JS)
 * Lightweight (< 8 KB, < 50ms execution). Zero external dependencies.
 */
(function() {
  'use strict';

  function initDepositWidget() {
    var container = document.getElementById('northwind-deposit-widget');
    if (!container) return;

    var productId = container.dataset.productId;
    var price = container.dataset.price;
    var currency = container.dataset.currency || 'AUD';
    var collections = container.dataset.collections || '';
    var shopDomain = window.Shopify ? window.Shopify.shop : window.location.hostname;

    if (!productId || !price) return;

    var queryUrl = '/apps/pay/plan-info?shop=' + encodeURIComponent(shopDomain)
      + '&product_id=' + encodeURIComponent(productId)
      + '&price=' + encodeURIComponent(price)
      + '&currency=' + encodeURIComponent(currency)
      + '&collection_ids=' + encodeURIComponent(collections);

    fetch(queryUrl, {
      headers: { 'Accept': 'application/json' }
    })
    .then(function(res) {
      if (!res.ok) throw new Error('Proxy returned ' + res.status);
      return res.json();
    })
    .then(function(data) {
      if (!data || !data.eligible || !data.plan) {
        container.style.display = 'none';
        return;
      }
      renderWidget(container, data, shopDomain, productId);
    })
    .catch(function(err) {
      // Non-intrusive fallback: silently hide widget if endpoint unavailable
      console.warn('[Northwind Ceramics] Deposit widget idle:', err.message);
      container.style.display = 'none';
    });
  }

  function renderWidget(container, data, shopDomain, productId) {
    var plan = data.plan;
    var calc = data.calculation;

    var html = `
      <div class="northwind-deposit-card" role="region" aria-label="Pre-order Payment Options">
        <div class="northwind-badge-lead-time">
          <span>&#9874;</span> ${plan.lead_time || 'Handmade: 3–6 weeks lead time'}
        </div>

        <div class="northwind-options-group">
          <!-- Full Payment Option -->
          <label class="northwind-option-label" id="nw-label-full">
            <input type="radio" name="northwind_payment_mode" value="full" class="northwind-radio-input" checked>
            <div class="northwind-option-content">
              <div class="northwind-option-title">
                <span>Pay in Full</span>
                <span>${calc.formatted_total}</span>
              </div>
              <div class="northwind-option-desc">Standard order fulfillment</div>
            </div>
          </label>

          <!-- Deposit Option -->
          <label class="northwind-option-label selected" id="nw-label-deposit">
            <input type="radio" name="northwind_payment_mode" value="deposit" class="northwind-radio-input">
            <div class="northwind-option-content">
              <div class="northwind-option-title">
                <span>Pay Pre-order Deposit</span>
                <span>${calc.formatted_deposit}</span>
              </div>
              <div class="northwind-option-desc">${plan.name}</div>
            </div>
          </label>
        </div>

        <!-- Deposit Breakdown -->
        <div class="northwind-deposit-breakdown" id="nw-breakdown-box">
          <div class="northwind-breakdown-col">
            <span class="northwind-breakdown-label">Due Today (Deposit)</span>
            <span class="northwind-breakdown-value">${calc.formatted_deposit}</span>
          </div>
          <div class="northwind-breakdown-col" style="text-align: right;">
            <span class="northwind-breakdown-label">Due Prior to Dispatch</span>
            <span class="northwind-breakdown-value" style="color: #4b5563;">${calc.formatted_balance}</span>
          </div>
        </div>

        <div class="northwind-policy-note">
          Deposits are non-refundable once making begins. Remaining balance billed via secure payment link prior to shipping.
        </div>
      </div>
    `;

    container.innerHTML = html;
    container.style.display = 'block';

    var radioDeposit = container.querySelector('input[value="deposit"]');
    var radioFull = container.querySelector('input[value="full"]');
    var labelDeposit = document.getElementById('nw-label-deposit');
    var labelFull = document.getElementById('nw-label-full');
    var breakdownBox = document.getElementById('nw-breakdown-box');

    // Default select deposit option
    radioDeposit.checked = true;
    labelDeposit.classList.add('selected');
    labelFull.classList.remove('selected');
    breakdownBox.style.display = 'flex';

    function updateSelection() {
      if (radioDeposit.checked) {
        labelDeposit.classList.add('selected');
        labelFull.classList.remove('selected');
        breakdownBox.style.display = 'flex';
      } else {
        labelFull.classList.add('selected');
        labelDeposit.classList.remove('selected');
        breakdownBox.style.display = 'none';
      }
    }

    radioDeposit.addEventListener('change', updateSelection);
    radioFull.addEventListener('change', updateSelection);

    // Intercept enclosing product form submit when deposit mode is selected
    var productForm = container.closest('form[action*="/cart/add"]') || document.querySelector('form[action*="/cart/add"]');
    if (productForm) {
      productForm.addEventListener('submit', function(e) {
        if (radioDeposit.checked) {
          e.preventDefault();
          var variantInput = productForm.querySelector('[name="id"]');
          var variantId = variantInput ? variantInput.value : '';
          var qtyInput = productForm.querySelector('[name="quantity"]');
          var qty = qtyInput ? qtyInput.value : '1';

          var checkoutUrl = '/apps/pay/checkout?shop=' + encodeURIComponent(shopDomain)
            + '&product_id=' + encodeURIComponent(productId)
            + '&variant_id=' + encodeURIComponent(variantId)
            + '&plan_id=' + encodeURIComponent(plan.id)
            + '&quantity=' + encodeURIComponent(qty)
            + '&price=' + encodeURIComponent(calc.total_cents)
            + '&currency=' + encodeURIComponent(calc.currency);

          window.location.href = checkoutUrl;
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDepositWidget);
  } else {
    initDepositWidget();
  }
})();
