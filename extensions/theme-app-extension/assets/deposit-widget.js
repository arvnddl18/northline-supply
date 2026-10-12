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
    var isInstallment = Boolean(calc.is_installment || plan.type === 'installment');

    var breakdownContent = '';
    if (isInstallment && calc.schedule && calc.schedule.length > 0) {
      breakdownContent = `
        <div style="width: 100%;">
          <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #1f2937;">Installment Payment Schedule</div>
          ${calc.schedule.map(function(s) {
            return `
              <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #e5e7eb;">
                <span style="color: #4b5563;">${s.due_label}</span>
                <strong style="color: #111827;">${s.formatted_amount}</strong>
              </div>
            `;
          }).join('')}
        </div>
      `;
    } else {
      breakdownContent = `
        <div class="northwind-breakdown-col">
          <span class="northwind-breakdown-label">Due Today (Deposit)</span>
          <span class="northwind-breakdown-value">${calc.formatted_deposit}</span>
        </div>
        <div class="northwind-breakdown-col" style="text-align: right;">
          <span class="northwind-breakdown-label">Due Prior to Dispatch</span>
          <span class="northwind-breakdown-value" style="color: #4b5563;">${calc.formatted_balance}</span>
        </div>
      `;
    }

    var policyText = isInstallment
      ? 'First installment charged at checkout. Remaining installments automatically charged to your saved card on schedule.'
      : 'Deposits are non-refundable once making begins. Remaining balance billed via secure payment link prior to shipping.';

    var optionTitle = isInstallment
      ? `Pay in ${plan.installments_count || 3} Installments`
      : 'Pay Pre-order Deposit';

    var optionPrice = isInstallment
      ? `${calc.formatted_first_installment} today`
      : calc.formatted_deposit;

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

          <!-- Deposit / Installment Option -->
          <label class="northwind-option-label selected" id="nw-label-deposit">
            <input type="radio" name="northwind_payment_mode" value="deposit" class="northwind-radio-input">
            <div class="northwind-option-content">
              <div class="northwind-option-title">
                <span>${optionTitle}</span>
                <span>${optionPrice}</span>
              </div>
              <div class="northwind-option-desc">${plan.name}</div>
            </div>
          </label>
        </div>

        <!-- Breakdown Box -->
        <div class="northwind-deposit-breakdown" id="nw-breakdown-box" style="display: flex;">
          ${breakdownContent}
        </div>

        <div class="northwind-policy-note">
          ${policyText}
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

    // Default select deposit/installment option
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

    // Intercept enclosing product form submit when deposit/installment mode is selected
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
