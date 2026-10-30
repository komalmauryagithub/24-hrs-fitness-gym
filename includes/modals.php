<?php
/**
 * DARK MATTER — Elite Fitness
 * Shared Modals Partial (PHP Component)
 */

declare(strict_types=1);

require_once __DIR__ . '/Security.php';
use DarkMatter\Security;
?>

  <!-- ================= MODAL: QUICK 1-DAY PASS ================= -->
  <div id="quickPassModal" class="dm-modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="qpModalTitle">
    <div class="dm-modal-dialog">
      <div class="modal-header">
        <div>
          <span class="mono-tag mb-1">FREE TRIAL PASS</span>
          <h3 id="qpModalTitle" class="modal-title">GET YOUR 1-DAY PASS</h3>
        </div>
        <button type="button" class="modal-close-btn" data-close-modal aria-label="Close modal">✕</button>
      </div>
      <div class="modal-body">
        <div id="quickPassAlert" class="form-alert-banner"></div>

        <form id="quickPassForm" novalidate>
          <?= Security::csrfField() ?>
          <!-- Anti-bot honeypot -->
          <input type="text" name="dm_telemetry_check" style="display:none !important;" tabindex="-1" autocomplete="off">
          <input type="hidden" name="inquiry_type" value="quick-pass">

          <div class="form-group">
            <label class="form-label" for="qpName">Full Name <span class="req">*</span></label>
            <input type="text" id="qpName" name="name" class="form-control-dm" placeholder="e.g. Rahul Sharma" required>
            <div class="invalid-feedback-msg">Please enter your full name.</div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="qpEmail">Email Address <span class="req">*</span></label>
                <input type="email" id="qpEmail" name="email" class="form-control-dm" placeholder="rahul@gmail.com" required>
                <div class="invalid-feedback-msg">Valid email is required.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="qpPhone">Phone Number <span class="req">*</span></label>
                <input type="tel" id="qpPhone" name="phone" class="form-control-dm" placeholder="e.g. 9920120785" required>
                <div class="invalid-feedback-msg">Valid phone number is required.</div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="custom-checkbox-label">
              <input type="checkbox" id="qpAgree" required>
              <span>I agree to 24HRS Fitness gym guidelines, safety rules, and free pass terms.</span>
            </label>
            <div class="invalid-feedback-msg">Please accept gym terms to continue.</div>
          </div>

          <button type="submit" class="btn-dm btn-lime w-100 mt-3">
            <span>GET FREE 1-DAY PASS</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ================= MODAL: MEMBERSHIP CHECKOUT ================= -->
  <div id="membershipModal" class="dm-modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="modalSelectedPlan">
    <div class="dm-modal-dialog">
      <div class="modal-header">
        <div>
          <span class="mono-tag mb-1">JOIN 24HRS FITNESS</span>
          <h3 id="modalSelectedPlan" class="modal-title">PRO FITNESS</h3>
        </div>
        <button type="button" class="modal-close-btn" data-close-modal aria-label="Close modal">✕</button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center p-3 mb-4" style="background: rgba(0, 229, 255, 0.06); border: 1px solid var(--lime-border);">
          <div>
            <div id="modalPlanPrice" class="fw-bold text-white" style="font-size: 1.4rem; font-family: var(--font-display);">₹2,500 / month</div>
            <div id="modalBillingCycle" class="text-muted" style="font-size: 0.8rem; font-family: var(--font-mono);">Billed monthly • Most popular plan</div>
          </div>
          <span class="discount-badge">NO JOINING FEE</span>
        </div>

        <div id="checkoutFeedback" class="form-alert-banner"></div>

        <form id="membershipCheckoutForm" novalidate>
          <?= Security::csrfField() ?>
          <!-- Anti-bot honeypot -->
          <input type="text" name="dm_telemetry_check" style="display:none !important;" tabindex="-1" autocomplete="off">
          <input type="hidden" id="modalPlanSlugInput" name="plan_slug" value="pro-fitness">
          <input type="hidden" id="modalBillingCycleInput" name="billing_cycle" value="monthly">

          <div class="form-group">
            <label class="form-label" for="mbrName">Full Name <span class="req">*</span></label>
            <input type="text" id="mbrName" name="name" class="form-control-dm" placeholder="e.g. Rahul Sharma" required>
            <div class="invalid-feedback-msg">Please enter your full name.</div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="mbrEmail">Email Address <span class="req">*</span></label>
                <input type="email" id="mbrEmail" name="email" class="form-control-dm" placeholder="rahul@gmail.com" required>
                <div class="invalid-feedback-msg">Valid email is required.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="mbrPhone">Phone Number <span class="req">*</span></label>
                <input type="tel" id="mbrPhone" name="phone" class="form-control-dm" placeholder="e.g. 9920120785" required>
                <div class="invalid-feedback-msg">Valid phone number is required.</div>
              </div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="mbrStartDate">Preferred Joining Date <span class="req">*</span></label>
                <input type="date" id="mbrStartDate" name="preferred_start_date" class="form-control-dm" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                <div class="invalid-feedback-msg">Please select your preferred start date.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="mbrLocation">Gym Branch <span class="req">*</span></label>
                <select id="mbrLocation" name="location" class="form-control-dm" required>
                  <option value="Andheri West (Main Club & Flagship)">Andheri West (Main Club & Flagship)</option>
                  <option value="Bandra West (Elite Training Center)">Bandra West (Elite Training Center)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Payment Option Selection (Pillar 2: Online payment option) -->
          <div class="form-group mb-3">
            <label class="form-label">Payment Preference <span class="req">*</span></label>
            <div class="payment-options-grid">
              <label class="payment-option-pill active" id="payOptionDesk">
                <input type="radio" name="payment_preference" value="pay_at_gym" checked>
                <div class="payment-option-info">
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="payment-option-title">Pay at Gym Reception</span>
                    <span class="badge-mini">PAY AT DESK</span>
                  </div>
                  <span class="payment-option-sub">Pay via Cash, Card, or UPI on your first day at the gym</span>
                </div>
              </label>

              <label class="payment-option-pill" id="payOptionOnline">
                <input type="radio" name="payment_preference" value="online_instant">
                <div class="payment-option-info">
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="payment-option-title">Pay Online Now (UPI / Card)</span>
                    <span class="badge-mini lime">INSTANT PASS</span>
                  </div>
                  <span class="payment-option-sub">Instant digital membership confirmation and gym entry code</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Online Payment Simulator Details -->
          <div id="onlinePaymentDetails" class="online-payment-box d-none mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-lime fw-bold" style="font-size: 0.8rem; font-family: var(--font-mono);">ONLINE PAYMENT DETAILS</span>
              <span class="text-silver" style="font-size: 0.72rem; font-family: var(--font-mono);">SECURE SSL ENCRYPTED</span>
            </div>
            
            <div class="d-flex gap-2 mb-3">
              <button type="button" class="gateway-tab-btn active" data-gateway-method="UPI">UPI / GPay / PhonePe</button>
              <button type="button" class="gateway-tab-btn" data-gateway-method="Credit Card">Debit / Credit Card</button>
            </div>
            <input type="hidden" id="onlinePaymentMethodInput" name="online_payment_method" value="UPI">

            <div id="gatewayUpiPanel" class="text-center p-3" style="background: #080909; border: 1px solid var(--dm-border); border-radius: 4px;">
              <div class="text-muted mb-2" style="font-size: 0.75rem; font-family: var(--font-mono);">SCAN QR CODE OR PAY TO UPI ID</div>
              <div class="qr-code-wrapper mb-2">
                <svg width="120" height="120" viewBox="0 0 100 100" fill="#000" style="background: #fff; padding: 6px; border-radius: 4px;">
                  <rect x="0" y="0" width="30" height="30" fill="#000"/><rect x="5" y="5" width="20" height="20" fill="#fff"/><rect x="10" y="10" width="10" height="10" fill="#000"/>
                  <rect x="70" y="0" width="30" height="30" fill="#000"/><rect x="75" y="5" width="20" height="20" fill="#fff"/><rect x="80" y="10" width="10" height="10" fill="#000"/>
                  <rect x="0" y="70" width="30" height="30" fill="#000"/><rect x="5" y="75" width="20" height="20" fill="#fff"/><rect x="10" y="80" width="10" height="10" fill="#000"/>
                  <rect x="40" y="10" width="15" height="15" fill="#000"/><rect x="45" y="35" width="10" height="10" fill="#000"/><rect x="15" y="45" width="15" height="10" fill="#000"/>
                  <rect x="40" y="60" width="20" height="10" fill="#000"/><rect x="65" y="45" width="15" height="20" fill="#000"/><rect x="70" y="75" width="20" height="20" fill="#000"/>
                </svg>
              </div>
              <div class="text-lime fw-bold" style="font-family: var(--font-mono); font-size: 0.85rem;">24hrsfitness@upi</div>
              <div class="text-muted mt-1" style="font-size: 0.72rem;">Submit to confirm your online payment and membership</div>
            </div>

            <div id="gatewayCardPanel" class="d-none p-3" style="background: #080909; border: 1px solid var(--dm-border); border-radius: 4px;">
              <div class="form-group mb-2">
                <label class="form-label" style="font-size: 0.75rem;">Card Number</label>
                <input type="text" class="form-control-dm" placeholder="4242 •••• •••• 4242" value="4242 8819 0921 7734">
              </div>
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label" style="font-size: 0.75rem;">Expiry Date</label>
                  <input type="text" class="form-control-dm" placeholder="MM/YY" value="12/28">
                </div>
                <div class="col-6">
                  <label class="form-label" style="font-size: 0.75rem;">CVV</label>
                  <input type="password" class="form-control-dm" placeholder="CVV" value="981">
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="custom-checkbox-label">
              <input type="checkbox" id="mbrAgree" required>
              <span>I confirm my application details and agree to the membership rules of 24HRS Fitness.</span>
            </label>
            <div class="invalid-feedback-msg">Please check this box to confirm.</div>
          </div>

          <button type="submit" class="btn-dm btn-lime w-100 mt-3" id="mbrSubmitBtn">
            <span>CONFIRM & JOIN NOW</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ================= MODAL: ENROLLMENT CONFIRMATION RECEIPT ================= -->
  <div id="enrollmentSuccessModal" class="dm-modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="successModalTitle">
    <div class="dm-modal-dialog">
      <div class="modal-header">
        <div>
          <span class="mono-tag mb-1 text-lime">MEMBERSHIP CONFIRMED</span>
          <h3 id="successModalTitle" class="modal-title">WELCOME TO 24HRS FITNESS!</h3>
        </div>
        <button type="button" class="modal-close-btn" data-close-modal aria-label="Close modal">✕</button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <div style="display: inline-flex; align-items: center; justify-content: center; width: 68px; height: 68px; border-radius: 50%; background: rgba(0, 229, 255, 0.15); border: 2px solid var(--dm-lime); box-shadow: 0 0 20px rgba(0, 229, 255, 0.35);">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--dm-lime)" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
        </div>

        <h4 id="successAthleteName" class="text-white mb-1" style="font-family: var(--font-display); font-size: 1.5rem; letter-spacing: 0.05em;">—</h4>
        <div class="text-muted mb-3" style="font-size: 0.85rem;">Your membership application has been received successfully!</div>

        <div class="p-3 mb-4 text-start" style="background: #080909; border: 1px solid var(--dm-border); border-radius: 6px;">
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: var(--dm-border) !important;">
            <span class="text-muted" style="font-size: 0.78rem; font-family: var(--font-mono);">MEMBERSHIP ID</span>
            <span id="successMemberId" class="text-lime fw-bold" style="font-family: var(--font-mono); font-size: 1.1rem; letter-spacing: 0.08em;">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size: 0.8rem;">Selected Plan:</span>
            <span id="successPlanName" class="text-white fw-bold">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size: 0.8rem;">Contact Phone:</span>
            <span id="successPhone" class="text-white">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size: 0.8rem;">Gym Branch:</span>
            <span id="successBranch" class="text-white">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size: 0.8rem;">Joining Date:</span>
            <span id="successStartDate" class="text-white">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size: 0.8rem;">Amount Payable:</span>
            <span id="successAmount" class="text-lime fw-bold">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted" style="font-size: 0.8rem;">Payment:</span>
            <span id="successPaymentStatus" class="badge-mini lime">PENDING</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted" style="font-size: 0.8rem;">Gym Access:</span>
            <span class="text-silver" style="font-size: 0.8rem;">Open 24/7 Unlimited Access</span>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="button" class="btn-dm btn-lime w-100" data-close-modal>
            <span>DONE / CLOSE</span>
          </button>
          <a id="successWaBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn-dm btn-outline-lime w-100">
            <span>WHATSAPP PASS</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MODAL: PROTOCOL INSPECTION ================= -->
  <div id="protocolModal" class="dm-modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="modalProtoTitle">
    <div class="dm-modal-dialog">
      <div class="modal-header">
        <div>
          <span id="modalProtoCategory" class="mono-tag mb-1">STRENGTH & MUSCLE</span>
          <h3 id="modalProtoTitle" class="modal-title">WORKOUT PROGRAM DETAILS</h3>
        </div>
        <button type="button" class="modal-close-btn" data-close-modal aria-label="Close modal">✕</button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-4">
          <div class="col-6">
            <div class="p-2" style="background: var(--bg-card); border: 1px solid var(--border-metallic);">
              <span class="metric-label">INTENSITY</span>
              <div id="modalProtoIntensity" class="fw-bold text-lime mt-1">4.5 / 5.0</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2" style="background: var(--bg-card); border: 1px solid var(--border-metallic);">
              <span class="metric-label">SESSION LENGTH</span>
              <div id="modalProtoDuration" class="fw-bold text-white mt-1">60 Minutes</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2" style="background: var(--bg-card); border: 1px solid var(--border-metallic);">
              <span class="metric-label">ESTIMATED BURN</span>
              <div id="modalProtoCalories" class="fw-bold text-white mt-1">600 – 800 kcal</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2" style="background: var(--bg-card); border: 1px solid var(--border-metallic);">
              <span class="metric-label">TRAINER RATIO</span>
              <div id="modalProtoRatio" class="fw-bold text-white mt-1">1 Trainer : 6 Members</div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <h4 class="text-lime fw-bold" style="font-family: var(--font-mono); font-size: 0.8rem; letter-spacing: 0.1em;">GOALS & BENEFITS:</h4>
          <p id="modalProtoAdaptation" class="text-silver" style="font-size: 0.92rem;"></p>
        </div>

        <div class="mb-3">
          <h4 class="text-lime fw-bold" style="font-family: var(--font-mono); font-size: 0.8rem; letter-spacing: 0.1em;">EQUIPMENT USED:</h4>
          <p id="modalProtoStations" class="text-muted" style="font-size: 0.9rem;"></p>
        </div>

        <div class="mb-4">
          <h4 class="text-lime fw-bold" style="font-family: var(--font-mono); font-size: 0.8rem; letter-spacing: 0.1em;">RECOMMENDED SCHEDULE:</h4>
          <p id="modalProtoCadence" class="text-silver" style="font-size: 0.92rem;"></p>
        </div>

        <div class="d-flex gap-3">
          <a href="membership.php" class="btn-dm btn-lime flex-grow-1">
            <span>VIEW MEMBERSHIP PLANS</span>
          </a>
          <button type="button" class="btn-dm btn-metallic" data-close-modal data-open-modal="quickPassModal">
            <span>GET FREE TRIAL PASS</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MODAL: TRAINER CONSULTATION ================= -->
  <div id="trainerModal" class="dm-modal-backdrop" aria-hidden="true" role="dialog" aria-labelledby="consultModalHeading">
    <div class="dm-modal-dialog">
      <div class="modal-header">
        <div>
          <span id="consultTrainerSpec" class="mono-tag mb-1">STRENGTH & FITNESS</span>
          <h3 id="consultModalHeading" class="modal-title">BOOK FREE CONSULTATION</h3>
        </div>
        <button type="button" class="modal-close-btn" data-close-modal aria-label="Close modal">✕</button>
      </div>
      <div class="modal-body">
        <div class="p-3 mb-4" style="background: rgba(0, 229, 255, 0.06); border: 1px solid var(--lime-border);">
          <div class="text-muted" style="font-family: var(--font-mono); font-size: 0.75rem;">PERSONAL TRAINER:</div>
          <div id="consultTrainerName" class="fw-bold text-lime" style="font-family: var(--font-display); font-size: 1.35rem;">MARCUS VANCE</div>
        </div>

        <div id="trainerConsultAlert" class="form-alert-banner"></div>

        <form id="trainerConsultForm" novalidate>
          <?= Security::csrfField() ?>
          <!-- Anti-bot honeypot -->
          <input type="text" name="dm_telemetry_check" style="display:none !important;" tabindex="-1" autocomplete="off">
          <input type="hidden" name="inquiry_type" value="trainer-consult">
          <input type="hidden" id="selectedTrainerInput" name="assigned_trainer" value="Marcus Vance">

          <div class="form-group">
            <label class="form-label" for="consultClientName">Full Name <span class="req">*</span></label>
            <input type="text" id="consultClientName" name="name" class="form-control-dm" placeholder="e.g. Rahul Sharma" required>
            <div class="invalid-feedback-msg">Please enter your full name.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="consultClientEmail">Email Address <span class="req">*</span></label>
            <input type="email" id="consultClientEmail" name="email" class="form-control-dm" placeholder="rahul@gmail.com" required>
            <div class="invalid-feedback-msg">Valid contact email is required.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="consultClientPhone">Phone Number <span class="req">*</span></label>
            <input type="tel" id="consultClientPhone" name="phone" class="form-control-dm" placeholder="e.g. 9920120785" required>
            <div class="invalid-feedback-msg">Valid phone number is required.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="consultGoal">Your Primary Fitness Goal <span class="req">*</span></label>
            <select id="consultGoal" name="training_focus" class="form-control-dm" required>
              <option value="">Select your fitness goal</option>
              <option value="Weight Loss & Fat Burning">Weight Loss & Fat Burning</option>
              <option value="Muscle Building & Strength">Muscle Building & Strength</option>
              <option value="Body Toning & Flexibility">Body Toning & Flexibility</option>
              <option value="Cardio & Endurance Fitness">Cardio & Endurance Fitness</option>
              <option value="General Health & Mobility">General Health & Mobility</option>
            </select>
            <div class="invalid-feedback-msg">Please select an objective.</div>
          </div>

          <button type="submit" class="btn-dm btn-lime w-100 mt-3">
            <span>BOOK FREE CONSULTATION</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </button>
        </form>
      </div>
    </div>
  </div>
