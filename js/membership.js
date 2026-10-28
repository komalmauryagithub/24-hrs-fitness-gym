/**
 * DARK MATTER — Elite Fitness
 * Membership Plans, Pricing Toggle & Real Backend Checkout Integration
 * Reads live database pricing from server and registers applications into MySQL.
 */

document.addEventListener('DOMContentLoaded', () => {
  loadServerPricingData();
  initPricingToggle();
  initFaqAccordion();
  initPlanSelectionModal();
});

// Fallback pricing dataset in case database JSON payload is not present
let PRICING_DATA = {
  monthly: {
    initiate: { price: 1500, note: 'Billed monthly • Cancel anytime' },
    obsidian: { price: 2500, note: 'Billed monthly • Most popular plan' },
    'black-matter': { price: 4500, note: 'Billed monthly • All-inclusive VIP' }
  },
  annual: {
    initiate: { price: 1200, note: 'Billed annually (₹14,400/yr) • Save 20%' },
    obsidian: { price: 2000, note: 'Billed annually (₹24,000/yr) • Save 20%' },
    'black-matter': { price: 3600, note: 'Billed annually (₹43,200/yr) • Save 20%' }
  }
};

let currentBillingCycle = 'monthly';
let selectedPlanSlug = 'obsidian';

/**
 * Load Dynamic Pricing Data from server JSON script tag if present
 */
function loadServerPricingData() {
  const serverDataEl = document.getElementById('serverPricingData');
  if (serverDataEl && serverDataEl.textContent.trim()) {
    try {
      const parsed = JSON.parse(serverDataEl.textContent.trim());
      if (parsed.monthly && parsed.annual) {
        PRICING_DATA = parsed;
      }
    } catch (e) {
      console.warn('Could not parse server pricing data, using defaults.', e);
    }
  }
}

/**
 * Monthly vs Annual Pricing Switch
 */
function initPricingToggle() {
  const switchPill = document.getElementById('billingSwitch');
  const labelMonthly = document.getElementById('labelMonthly');
  const labelAnnual = document.getElementById('labelAnnual');

  if (!switchPill) return;

  const toggleBilling = (cycle) => {
    currentBillingCycle = cycle;

    if (cycle === 'annual') {
      switchPill.classList.add('annual-active');
      if (labelAnnual) labelAnnual.classList.add('active');
      if (labelMonthly) labelMonthly.classList.remove('active');
    } else {
      switchPill.classList.remove('annual-active');
      if (labelMonthly) labelMonthly.classList.add('active');
      if (labelAnnual) labelAnnual.classList.remove('active');
    }

    updatePriceDisplay(cycle);
  };

  switchPill.addEventListener('click', () => {
    toggleBilling(currentBillingCycle === 'monthly' ? 'annual' : 'monthly');
  });

  if (labelMonthly) {
    labelMonthly.addEventListener('click', () => toggleBilling('monthly'));
  }
  if (labelAnnual) {
    labelAnnual.addEventListener('click', () => toggleBilling('annual'));
  }
}

function updatePriceDisplay(cycle) {
  const data = PRICING_DATA[cycle];
  if (!data) return;

  for (const [slug, planInfo] of Object.entries(data)) {
    const priceEl = document.getElementById(`price-${slug}`);
    const noteEl = document.getElementById(`note-${slug}`);

    if (priceEl && planInfo.price !== undefined) {
      priceEl.textContent = planInfo.price;
    }
    if (noteEl && planInfo.note !== undefined) {
      noteEl.textContent = planInfo.note;
    }
  }
}

/**
 * FAQ Accordion Expand/Collapse
 */
function initFaqAccordion() {
  const accordions = document.querySelectorAll('.accordion-header');

  accordions.forEach(header => {
    header.addEventListener('click', () => {
      const item = header.parentElement;
      const body = item.querySelector('.accordion-body');
      const isActive = item.classList.contains('active');

      // Close all sibling items
      const siblingItems = item.parentElement.querySelectorAll('.accordion-item');
      siblingItems.forEach(sib => {
        if (sib !== item) {
          sib.classList.remove('active');
          const sibBody = sib.querySelector('.accordion-body');
          if (sibBody) sibBody.style.maxHeight = null;
        }
      });

      if (isActive) {
        item.classList.remove('active');
        body.style.maxHeight = null;
      } else {
        item.classList.add('active');
        body.style.maxHeight = body.scrollHeight + 'px';
      }
    });
  });
}

/**
 * Plan Selection Modal & Real Backend Application Integration
 */
function initPlanSelectionModal() {
  const selectButtons = document.querySelectorAll('.btn-select-plan');
  const modalPlanTitle = document.getElementById('modalSelectedPlan');
  const modalPlanPrice = document.getElementById('modalPlanPrice');
  const modalBillingCycle = document.getElementById('modalBillingCycle');
  const modalPlanSlugInput = document.getElementById('modalPlanSlugInput');
  const modalBillingCycleInput = document.getElementById('modalBillingCycleInput');
  const checkoutForm = document.getElementById('membershipCheckoutForm');
  const checkoutFeedback = document.getElementById('checkoutFeedback');

  selectButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      selectedPlanSlug = btn.getAttribute('data-plan') || 'obsidian';
      const planName = btn.getAttribute('data-plan-name') || 'OBSIDIAN';
      const price = PRICING_DATA[currentBillingCycle][selectedPlanSlug]?.price || 2500;

      if (modalPlanTitle) modalPlanTitle.textContent = planName;
      if (modalPlanPrice) modalPlanPrice.textContent = `₹${price} / month`;
      if (modalBillingCycle) {
        modalBillingCycle.textContent = currentBillingCycle === 'annual' 
          ? 'Billed annually (Save 20%)' 
          : 'Billed monthly';
      }
      if (modalPlanSlugInput) modalPlanSlugInput.value = selectedPlanSlug;
      if (modalBillingCycleInput) modalBillingCycleInput.value = currentBillingCycle;

      if (window.openModal) {
        window.openModal('membershipModal', btn);
      }
    });
  });
}

