/**
 * DARK MATTER — Elite Fitness
 * Unified Form Validation, Mode Switcher, and Real Backend API Integration
 * Connects asynchronously to PHP 8.3+ endpoints with CSRF, Honeypot & Rate Limiting.
 */

document.addEventListener('DOMContentLoaded', () => {
  initContactModeTabs();
  initContactForm();
  initTrialPassModalForm();
  initTrainerConsultForm();
  initWhatsAppCTAs();
  initNewsletterAjax();
  initMembershipCheckoutForm();
});

/**
 * Helper: Retrieve the active CSRF token from meta tag or input
 */
function getActiveCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta && meta.content) {
    return meta.content;
  }
  const input = document.querySelector('input[name="csrf_token"]');
  return input ? input.value : '';
}

/**
 * Helper: Update CSRF tokens across the DOM when a new token is issued
 */
function updateActiveCsrfToken(newToken) {
  if (!newToken) return;
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta) {
    meta.content = newToken;
  }
  document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
    input.value = newToken;
  });
}

/**
 * Contact Mode Switcher (VIP Pass / Facility Tour / General Inquiry)
 */
function initContactModeTabs() {
  const modeTabs = document.querySelectorAll('.contact-mode-tab');
  const inquiryTypeInput = document.getElementById('inquiryType');
  const formHeading = document.getElementById('contactFormHeading');
  const formSubheading = document.getElementById('contactFormSubheading');
  const submitBtnText = document.getElementById('contactSubmitBtnText');
  const extraFieldsWrap = document.getElementById('dynamicExtraFields');

  if (!modeTabs.length) return;

  const initialDate = document.getElementById('preferredDate');
  if (initialDate) {
    initialDate.min = new Date().toISOString().split('T')[0];
  }

  const modeConfigs = {
    'vip-pass': {
      title: 'CLAIM FREE 1-DAY WORKOUT PASS',
      subtitle: 'Experience our gym floor, weights, cardio machines, and steam sauna for one workout session.',
      btnText: 'GET FREE 1-DAY PASS',
      extraField: `
        <div class="form-group">
          <label class="form-label" for="preferredDate">Preferred Visit Date <span class="req">*</span></label>
          <input type="date" id="preferredDate" name="preferred_date" class="form-control-dm" required min="${new Date().toISOString().split('T')[0]}">
          <div class="invalid-feedback-msg">Please choose your intended visit date.</div>
        </div>
      `
    },
    'facility-tour': {
      title: 'BOOK A FREE GYM TOUR',
      subtitle: 'Take a quick walkthrough with our coaches to explore our workout zones and facilities.',
      btnText: 'BOOK FREE GYM TOUR',
      extraField: `
        <div class="form-group">
          <label class="form-label" for="tourTimeSlot">Preferred Tour Time Slot <span class="req">*</span></label>
          <select id="tourTimeSlot" name="preferred_time_slot" class="form-control-dm" required>
            <option value="">Select a time slot</option>
            <option value="morning">Morning (07:00 AM – 10:00 AM)</option>
            <option value="midday">Afternoon (11:00 AM – 02:00 PM)</option>
            <option value="evening">Evening (05:00 PM – 08:00 PM)</option>
            <option value="night">Night (08:00 PM – 10:00 PM)</option>
          </select>
          <div class="invalid-feedback-msg">Please select an available tour time slot.</div>
        </div>
      `
    },
    'general-inquiry': {
      title: 'SEND US A MESSAGE',
      subtitle: 'Have a question? Send our team a message and we will get back to you shortly.',
      btnText: 'SEND MESSAGE',
      extraField: ''
    }
  };

  modeTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      modeTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const mode = tab.getAttribute('data-mode') || 'vip-pass';
      const config = modeConfigs[mode];

      if (inquiryTypeInput) inquiryTypeInput.value = mode;
      if (formHeading) formHeading.textContent = config.title;
      if (formSubheading) formSubheading.textContent = config.subtitle;
      if (submitBtnText) submitBtnText.textContent = config.btnText;
      if (extraFieldsWrap) extraFieldsWrap.innerHTML = config.extraField;
    });
  });
}

/**
 * Main Contact Form Validation & Real Backend Submission
 */
function initContactForm() {
  const form = document.getElementById('mainContactForm');
  const alertBanner = document.getElementById('contactFormAlert');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const nameInput = form.querySelector('#contactName');
    const emailInput = form.querySelector('#contactEmail');
    const phoneInput = form.querySelector('#contactPhone');
    const locationInput = form.querySelector('#contactLocation');
    const messageInput = form.querySelector('#contactMessage');
    const consentInput = form.querySelector('#contactConsent');
    const dynamicDate = form.querySelector('#preferredDate');
    const dynamicSlot = form.querySelector('#tourTimeSlot');
    const inquiryType = form.querySelector('#inquiryType')?.value || 'vip-pass';
    const honeypot = form.querySelector('input[name="dm_telemetry_check"]')?.value || '';

    let isValid = true;

    [nameInput, emailInput, phoneInput, locationInput].forEach(field => {
      if (!field || !field.value.trim() || !field.checkValidity()) {
        if (field) field.classList.add('is-invalid');
        isValid = false;
      } else {
        field.classList.remove('is-invalid');
      }
    });

    if (dynamicDate && (!dynamicDate.value.trim() || !dynamicDate.checkValidity())) {
      dynamicDate.classList.add('is-invalid');
      isValid = false;
    } else if (dynamicDate) {
      dynamicDate.classList.remove('is-invalid');
    }

    if (dynamicSlot && !dynamicSlot.value) {
      dynamicSlot.classList.add('is-invalid');
      isValid = false;
    } else if (dynamicSlot) {
      dynamicSlot.classList.remove('is-invalid');
    }

    if (consentInput && !consentInput.checked) {
      consentInput.classList.add('is-invalid');
      isValid = false;
    } else if (consentInput) {
      consentInput.classList.remove('is-invalid');
    }

    if (!isValid) {
      if (alertBanner) {
        alertBanner.className = 'form-alert-banner error';
        alertBanner.innerHTML = '<strong>PLEASE COMPLETE ALL FIELDS:</strong> Please fill in all required fields before submitting.';
        alertBanner.style.display = 'block';
      }
      return;
    }

    // Submit via Fetch to PHP Backend
    const submitBtn = form.querySelector('button[type="submit"]');
    const origHtml = submitBtn.innerHTML;
    submitBtn.innerHTML = `<span>SENDING YOUR REQUEST...</span>`;
    submitBtn.disabled = true;

    const payload = {
      csrf_token: getActiveCsrfToken(),
      dm_telemetry_check: honeypot,
      inquiry_type: inquiryType,
      full_name: nameInput.value.trim(),
      email: emailInput.value.trim(),
      phone: phoneInput.value.trim(),
      location: locationInput.value,
      preferred_date: dynamicDate ? dynamicDate.value : null,
      preferred_time_slot: dynamicSlot ? dynamicSlot.value : null,
      message: messageInput ? messageInput.value.trim() : null
    };

    try {
      const response = await fetch('api/enquiry.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': getActiveCsrfToken()
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (response.ok && result.success) {
        const refCode = result.data?.reference_code || '#LOGGED';
        const athlete = result.data?.athlete_name || payload.full_name;

        if (result.data?.new_csrf_token) {
          updateActiveCsrfToken(result.data.new_csrf_token);
        }

        if (alertBanner) {
          alertBanner.className = 'form-alert-banner success';
          alertBanner.innerHTML = `
            <strong>REQUEST SUBMITTED SUCCESSFULLY!</strong><br>
            Reference Number: <code style="color: var(--lime-accent); background: #000; padding: 2px 6px;">${refCode}</code><br>
            Name: <strong>${athlete}</strong> (${payload.email})<br>
            Inquiry Type: <strong>${inquiryType.toUpperCase()}</strong><br>
            <div style="margin-top: 8px; font-size: 0.85rem; color: var(--text-muted); border-top: 1px solid rgba(0, 229, 255, 0.2); padding-top: 8px;">
              Thank you! Our gym team will contact you shortly to confirm your booking.
            </div>
          `;
          alertBanner.style.display = 'block';
        }

        form.reset();
        if (window.showToast) {
          window.showToast('REQUEST SUBMITTED', `Reference ${refCode} received for ${athlete}.`);
        }
      } else {
        const errorMsg = result.errors ? result.errors.join('<br>') : (result.message || 'Submission failed.');
        if (alertBanner) {
          alertBanner.className = 'form-alert-banner error';
          alertBanner.innerHTML = `<strong>SUBMISSION ERROR:</strong><br>${errorMsg}`;
          alertBanner.style.display = 'block';
        }
      }
    } catch (err) {
      if (alertBanner) {
        alertBanner.className = 'form-alert-banner error';
        alertBanner.innerHTML = `<strong>CONNECTION ERROR:</strong> Unable to send request. Please check your internet connection or try again.`;
        alertBanner.style.display = 'block';
      }
    } finally {
      submitBtn.innerHTML = origHtml;
      submitBtn.disabled = false;
    }
  });
}

/**
 * Free Trial Pass Modal Form (Real Backend AJAX)
 */
function initTrialPassModalForm() {
  const form = document.getElementById('quickPassForm');
  const alertBanner = document.getElementById('quickPassAlert');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = form.querySelector('#qpName');
    const email = form.querySelector('#qpEmail');
    const phone = form.querySelector('#qpPhone');
    const agree = form.querySelector('#qpAgree');
    const honeypot = form.querySelector('input[name="dm_telemetry_check"]')?.value || '';

    let isValid = true;
    [name, email, phone].forEach(input => {
      if (!input || !input.value.trim() || !input.checkValidity()) {
        input.classList.add('is-invalid');
        isValid = false;
      } else {
        input.classList.remove('is-invalid');
      }
    });

    if (agree && !agree.checked) {
      agree.classList.add('is-invalid');
      isValid = false;
    } else if (agree) {
      agree.classList.remove('is-invalid');
    }

    if (!isValid) return;

    const btn = form.querySelector('button[type="submit"]');
    const origText = btn.innerHTML;
    btn.innerHTML = '<span>GENERATING FREE PASS...</span>';
    btn.disabled = true;

    const payload = {
      csrf_token: getActiveCsrfToken(),
      dm_telemetry_check: honeypot,
      inquiry_type: 'quick-pass',
      full_name: name.value.trim(),
      email: email.value.trim(),
      phone: phone.value.trim(),
      location: 'Main Branch — City Center'
    };

    try {
      const response = await fetch('api/enquiry.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': getActiveCsrfToken()
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (response.ok && result.success) {
        const passCode = result.data?.reference_code || '#PASS-CONFIRMED';
        const athlete = result.data?.athlete_name || payload.full_name;

        if (result.data?.new_csrf_token) {
          updateActiveCsrfToken(result.data.new_csrf_token);
        }

        if (alertBanner) {
          alertBanner.className = 'form-alert-banner success';
          alertBanner.innerHTML = `
            <strong>FREE 1-DAY PASS ISSUED!</strong><br>
            Passholder: <strong>${athlete}</strong><br>
            Pass Code: <code style="color: var(--lime-accent); background: #000; padding: 2px 6px;">${passCode}</code><br>
            <small style="color: var(--text-muted); display: block; margin-top: 6px;">Please show this pass code at our reception upon your visit.</small>
          `;
          alertBanner.style.display = 'block';
        }

        form.reset();
        if (window.showToast) {
          window.showToast('PASS READY', `Your free 1-day pass code is ${passCode}.`);
        }
      } else {
        const msg = result.errors ? result.errors.join('<br>') : (result.message || 'Pass generation failed.');
        if (alertBanner) {
          alertBanner.className = 'form-alert-banner error';
          alertBanner.innerHTML = `<strong>REGISTRATION ERROR:</strong><br>${msg}`;
          alertBanner.style.display = 'block';
        }
      }
    } catch (err) {
      if (alertBanner) {
        alertBanner.className = 'form-alert-banner error';
        alertBanner.innerHTML = `<strong>CONNECTION ERROR:</strong> Could not connect to gym server. Please try again.`;
        alertBanner.style.display = 'block';
      }
    } finally {
      btn.innerHTML = origText;
      btn.disabled = false;
    }
  });
}

/**
 * Trainer Consultation Form Modal (Real Backend AJAX)
 */
function initTrainerConsultForm() {
  const consultBtns = document.querySelectorAll('.btn-book-trainer');
  const modal = document.getElementById('trainerModal');
  const trainerNameDisplay = document.getElementById('consultTrainerName');
  const trainerSpecDisplay = document.getElementById('consultTrainerSpec');
  const trainerInput = document.getElementById('selectedTrainerInput');
  const consultForm = document.getElementById('trainerConsultForm');
  const alertBanner = document.getElementById('trainerConsultAlert');

  consultBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const name = btn.getAttribute('data-trainer-name') || 'Trainer';
      const spec = btn.getAttribute('data-trainer-spec') || 'Personal Coach';

      if (trainerNameDisplay) trainerNameDisplay.textContent = name;
      if (trainerSpecDisplay) trainerSpecDisplay.textContent = spec;
      if (trainerInput) trainerInput.value = name;

      if (window.openModal) {
        window.openModal('trainerModal', btn);
      }
    });
  });

  if (!consultForm) return;

  consultForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = consultForm.querySelector('#consultClientName');
    const email = consultForm.querySelector('#consultClientEmail');
    const phone = consultForm.querySelector('#consultClientPhone');
    const goal = consultForm.querySelector('#consultGoal');
    const trainer = consultForm.querySelector('#selectedTrainerInput')?.value || 'Trainer';
    const honeypot = consultForm.querySelector('input[name="dm_telemetry_check"]')?.value || '';

    let isValid = true;
    [name, email, phone, goal].forEach(input => {
      if (!input || !input.value.trim() || !input.checkValidity()) {
        input.classList.add('is-invalid');
        isValid = false;
      } else {
        input.classList.remove('is-invalid');
      }
    });

    if (!isValid) return;

    const btn = consultForm.querySelector('button[type="submit"]');
    const orig = btn.innerHTML;
    btn.innerHTML = '<span>BOOKING CONSULTATION...</span>';
    btn.disabled = true;

    const payload = {
      csrf_token: getActiveCsrfToken(),
      dm_telemetry_check: honeypot,
      inquiry_type: 'trainer-consult',
      full_name: name.value.trim(),
      email: email.value.trim(),
      phone: phone.value.trim(),
      location: 'Main Branch — City Center',
      assigned_trainer: trainer,
      training_focus: goal.value
    };

    try {
      const response = await fetch('api/enquiry.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': getActiveCsrfToken()
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (response.ok && result.success) {
        const ref = result.data?.reference_code || '#CONSULT';
        if (result.data?.new_csrf_token) {
          updateActiveCsrfToken(result.data.new_csrf_token);
        }

        if (alertBanner) {
          alertBanner.className = 'form-alert-banner success';
          alertBanner.innerHTML = `
            <strong>CONSULTATION REQUEST SUBMITTED!</strong><br>
            Personal Trainer: <strong>${trainer}</strong><br>
            Name: <strong>${payload.full_name}</strong><br>
            Reference Number: <code style="color: var(--lime-accent);">${ref}</code><br>
            <small style="color: var(--text-muted); display: block; margin-top: 6px;">Our trainer will get in touch with you to schedule your session.</small>
          `;
          alertBanner.style.display = 'block';
        }

        consultForm.reset();
        if (window.showToast) {
          window.showToast('BOOKING CONFIRMED', `Consultation request sent to ${trainer}.`);
        }
      } else {
        const msg = result.errors ? result.errors.join('<br>') : (result.message || 'Consultation booking failed.');
        if (alertBanner) {
          alertBanner.className = 'form-alert-banner error';
          alertBanner.innerHTML = `<strong>SUBMISSION FAILED:</strong><br>${msg}`;
          alertBanner.style.display = 'block';
        }
      }
    } catch (err) {
      if (alertBanner) {
        alertBanner.className = 'form-alert-banner error';
        alertBanner.innerHTML = `<strong>CONNECTION ERROR:</strong> Unable to connect to gym server.`;
        alertBanner.style.display = 'block';
      }
    } finally {
      btn.innerHTML = orig;
      btn.disabled = false;
    }
  });
}

/**
 * Newsletter Form Real AJAX Submission
 */
function initNewsletterAjax() {
  const forms = document.querySelectorAll('.newsletter-form');
  forms.forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = form.querySelector('input[type="email"]');
      const honeypot = form.querySelector('input[name="dm_telemetry_check"]')?.value || '';

      if (!input || !input.value.trim() || !input.checkValidity()) {
        if (input) {
          input.classList.add('is-invalid');
          input.focus();
        }
        return;
      }

      input.classList.remove('is-invalid');
      const email = input.value.trim();

      const btn = form.querySelector('button[type="submit"]');
      const origText = btn.innerHTML;
      btn.innerHTML = '<span>SUBSCRIBING...</span>';
      btn.disabled = true;

      try {
        const response = await fetch('api/enquiry.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getActiveCsrfToken()
          },
          body: JSON.stringify({
            csrf_token: getActiveCsrfToken(),
            dm_telemetry_check: honeypot,
            inquiry_type: 'newsletter',
            email: email
          })
        });

        const result = await response.json();
        if (response.ok && result.success) {
          input.value = '';
          if (window.showToast) {
            window.showToast('SUBSCRIBED', `You are now subscribed to gym news and updates.`);
          }
        } else {
          if (window.showToast) {
            window.showToast('SUBSCRIPTION FAILED', result.message || 'Could not register email.');
          }
        }
      } catch (err) {
        if (window.showToast) {
          window.showToast('ERROR', 'Unable to process subscription.');
        }
      } finally {
        btn.innerHTML = origText;
        btn.disabled = false;
      }
    });
  });
}

/**
 * WhatsApp Direct CTA Trigger
 */
function initWhatsAppCTAs() {
  const whatsappBtns = document.querySelectorAll('.btn-whatsapp-cta');

  whatsappBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const phone = btn.getAttribute('data-wa-phone') || '919920120785';
      const message = encodeURIComponent('Hello 24HRS FITNESS! I would like to inquire about gym membership plans and facilities.');
      const waUrl = `https://wa.me/${phone}?text=${message}`;

      if (window.showToast) {
        window.showToast('OPENING WHATSAPP', 'Connecting to 24HRS FITNESS WhatsApp...');
      }

      setTimeout(() => {
        window.open(waUrl, '_blank', 'noopener,noreferrer');
      }, 500);
    });
  });
}

/**
 * Membership Application / Checkout Modal (Online payment option & preferred date)
 */
function initMembershipCheckoutForm() {
  const form = document.getElementById('membershipCheckoutForm');
  if (!form) return;

  const payOptions = form.querySelectorAll('input[name="payment_preference"]');
  const onlineBox = document.getElementById('onlinePaymentDetails');
  const gatewayTabs = form.querySelectorAll('.gateway-tab-btn');
  const upiPanel = document.getElementById('gatewayUpiPanel');
  const cardPanel = document.getElementById('gatewayCardPanel');
  const methodInput = document.getElementById('onlinePaymentMethodInput');
  const submitBtn = document.getElementById('mbrSubmitBtn');
  const feedback = document.getElementById('checkoutFeedback');

  // Toggle payment preference
  payOptions.forEach(opt => {
    opt.addEventListener('change', () => {
      document.querySelectorAll('.payment-option-pill').forEach(p => p.classList.remove('active'));
      const parentLabel = opt.closest('.payment-option-pill');
      if (parentLabel) parentLabel.classList.add('active');

      if (opt.value === 'online_instant') {
        if (onlineBox) onlineBox.classList.remove('d-none');
        if (submitBtn) submitBtn.querySelector('span').textContent = 'PAY ONLINE & JOIN NOW';
      } else {
        if (onlineBox) onlineBox.classList.add('d-none');
        if (submitBtn) submitBtn.querySelector('span').textContent = 'CONFIRM & JOIN NOW';
      }
    });
  });

  // Toggle online gateway method tabs (UPI vs Card)
  gatewayTabs.forEach(tab => {
    tab.addEventListener('click', (e) => {
      e.preventDefault();
      gatewayTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const method = tab.getAttribute('data-gateway-method') || 'UPI';
      if (methodInput) methodInput.value = method;

      if (method === 'UPI') {
        if (upiPanel) upiPanel.classList.remove('d-none');
        if (cardPanel) cardPanel.classList.add('d-none');
      } else {
        if (cardPanel) cardPanel.classList.remove('d-none');
        if (upiPanel) upiPanel.classList.add('d-none');
      }
    });
  });

  // Form submission
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fullName = form.querySelector('#mbrName');
    const email = form.querySelector('#mbrEmail');
    const phone = form.querySelector('#mbrPhone');
    const startDate = form.querySelector('#mbrStartDate');
    const location = form.querySelector('#mbrLocation');
    const agree = form.querySelector('#mbrAgree');
    const planSlug = form.querySelector('#modalPlanSlugInput')?.value || 'starter';
    const billingCycle = form.querySelector('#modalBillingCycleInput')?.value || 'monthly';
    const paymentPref = form.querySelector('input[name="payment_preference"]:checked')?.value || 'pay_at_gym';
    const onlineMethod = methodInput ? methodInput.value : 'UPI';
    const honeypot = form.querySelector('input[name="dm_telemetry_check"]')?.value || '';

    let isValid = true;
    [fullName, email, phone, startDate, location].forEach(input => {
      if (!input || !input.value.trim() || !input.checkValidity()) {
        if (input) input.classList.add('is-invalid');
        isValid = false;
      } else {
        input.classList.remove('is-invalid');
      }
    });

    if (agree && !agree.checked) {
      agree.classList.add('is-invalid');
      isValid = false;
    } else if (agree) {
      agree.classList.remove('is-invalid');
    }

    if (!isValid) return;

    const origBtnHtml = submitBtn.innerHTML;
    submitBtn.innerHTML = `<span>PROCESSING YOUR APPLICATION...</span>`;
    submitBtn.disabled = true;

    const payload = {
      csrf_token: getActiveCsrfToken(),
      dm_telemetry_check: honeypot,
      plan_slug: planSlug,
      billing_cycle: billingCycle,
      full_name: fullName.value.trim(),
      email: email.value.trim(),
      phone: phone.value.trim(),
      preferred_start_date: startDate ? startDate.value : '',
      payment_preference: paymentPref,
      online_payment_method: onlineMethod,
      location: location ? location.value : 'Main Branch — City Center'
    };

    try {
      const response = await fetch('api/membership_apply.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': getActiveCsrfToken()
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (response.ok && result.success) {
        if (result.data?.new_csrf_token) {
          updateActiveCsrfToken(result.data.new_csrf_token);
        }

        // Close checkout modal
        if (window.closeModal) {
          window.closeModal(document.getElementById('membershipModal'));
        }

        // Populate Success Modal
        const successModal = document.getElementById('enrollmentSuccessModal');
        const athleteEl = document.getElementById('successAthleteName');
        const idEl = document.getElementById('successMemberId');
        const planEl = document.getElementById('successPlanName');
        const dateEl = document.getElementById('successStartDate');
        const statusEl = document.getElementById('successPaymentStatus');
        const waBtn = document.getElementById('successWaBtn');

        const memberId = result.data?.membership_code || 'PENDING';
        if (athleteEl) athleteEl.textContent = result.data?.applicant_name || payload.full_name;
        if (idEl) idEl.textContent = memberId;
        if (planEl) planEl.textContent = result.data?.plan_name || 'GYM MEMBERSHIP';
        if (dateEl) dateEl.textContent = result.data?.preferred_start_date || payload.preferred_start_date;

        if (statusEl) {
          if (result.data?.is_paid) {
            statusEl.className = 'badge-mini lime';
            statusEl.textContent = 'ACTIVE / ONLINE PAYMENT CONFIRMED';
          } else {
            statusEl.className = 'badge-mini';
            statusEl.textContent = 'RESERVED / PAY AT GYM RECEPTION';
          }
        }

        if (waBtn) {
          const waText = encodeURIComponent(`Hello 24HRS FITNESS! I have completed my membership application for ${result.data?.plan_name || 'Gym Membership'}. My Member ID is ${memberId}.`);
          waBtn.href = `https://wa.me/919920120785?text=${waText}`;
        }

        // Open Success Modal
        if (window.openModal && successModal) {
          window.openModal('enrollmentSuccessModal');
        }

        if (window.showToast) {
          window.showToast('MEMBERSHIP REGISTERED', `Welcome to 24HRS FITNESS! Member ID: ${memberId}`);
        }

        form.reset();
      } else {
        const msg = result.errors ? result.errors.join('<br>') : (result.message || 'Application failed.');
        if (feedback) {
          feedback.className = 'form-alert-banner error';
          feedback.innerHTML = `<strong>APPLICATION ERROR:</strong><br>${msg}`;
          feedback.style.display = 'block';
        }
      }
    } catch (err) {
      if (feedback) {
        feedback.className = 'form-alert-banner error';
        feedback.innerHTML = `<strong>CONNECTION ERROR:</strong> Unable to connect to gym server.`;
        feedback.style.display = 'block';
      }
    } finally {
      submitBtn.innerHTML = origBtnHtml;
      submitBtn.disabled = false;
    }
  });
}

