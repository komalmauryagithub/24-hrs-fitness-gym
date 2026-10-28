<?php
/**
 * DARK MATTER — Elite Fitness
 * Contact & Join (Phase 2 Dynamic PHP/MySQL)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Security.php';

use DarkMatter\Database;
use DarkMatter\Security;

$pageTitle = 'Contact Us & Gym Location — 24HRS FITNESS';
$pageDesc = 'Get in touch with 24HRS FITNESS. Claim a free 1-day pass, book a gym tour, or message us on WhatsApp and call our reception.';

// Fetch settings from database
try {
    $settingsRows = Database::fetchAll("SELECT `setting_key`, `setting_value` FROM `site_settings`");
    $settings = array_column($settingsRows, 'setting_value', 'setting_key');
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS CONTACT SETTINGS ERROR]: ' . $e->getMessage());
    $settings = [];
}

$siteAddress = $settings['address'] ?? 'Plot 42, Main Commercial Avenue, City Center, Mumbai, MH 400001';
$sitePhone = $settings['contact_phone'] ?? '+91 99201 20785';
$siteEmail = $settings['contact_email'] ?? 'info@fitnessgym.com';
$geoCoords = $settings['geo_coordinates'] ?? '19.0760° N, 72.8777° E';
$waNumber = $settings['whatsapp_number'] ?? '919920120785';

require_once __DIR__ . '/includes/header.php';
?>

  <main id="mainContent">

    <!-- ================= SUBPAGE HERO ================= -->
    <section class="subpage-hero">
      <div class="container-custom">
        <span class="mono-tag mb-2">GET IN TOUCH</span>
        <h1 class="display-title mb-3">CONTACT US & LOCATION</h1>
        <p class="lead text-silver" style="max-width: 680px;">
          Have questions about our memberships, want to book a free gym walkthrough, or claim your 1-day free pass? Our reception team is here to assist you.
        </p>
      </div>
    </section>

    <!-- ================= MAIN CONTACT & FORM SECTION ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        <div class="row g-5">
          
          <!-- LEFT COLUMN: GYM INFO & MAP -->
          <div class="col-lg-5">
            <div class="d-flex flex-column gap-4">
              
              <!-- Gym Info Card -->
              <div class="dm-card">
                <span class="mono-tag mb-2">OUR LOCATION & HOURS</span>
                <h2 class="card-title mb-4">24HRS FITNESS CLUB</h2>

                <div class="footer-contact-item mb-3">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lime-accent)" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                  <div>
                    <div class="fw-bold text-white">Gym Address</div>
                    <div class="text-muted" style="font-size: 0.9rem;"><?= e($siteAddress) ?></div>
                  </div>
                </div>

                <div class="footer-contact-item mb-3">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lime-accent)" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                  <div>
                    <div class="fw-bold text-white">Phone Number</div>
                    <div class="text-muted" style="font-size: 0.9rem;"><?= e($sitePhone) ?> (Call & SMS)</div>
                  </div>
                </div>

                <div class="footer-contact-item mb-3">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lime-accent)" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                  <div>
                    <div class="fw-bold text-white">Email Address</div>
                    <div class="text-muted" style="font-size: 0.9rem;"><?= e($siteEmail) ?></div>
                  </div>
                </div>

                <div class="footer-contact-item mb-4">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lime-accent)" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                  <div>
                    <div class="fw-bold text-white">Gym Operating Hours</div>
                    <div class="text-muted" style="font-size: 0.9rem;">Gym Floor: Open 24/7/365 Day & Night<br>Reception Desk: 06:00 AM – 10:00 PM Daily</div>
                  </div>
                </div>

                <!-- Instant Action Buttons: Call Now & WhatsApp -->
                <div class="d-flex flex-column gap-2 mb-2">
                  <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $sitePhone)) ?>" class="btn-dm btn-outline-lime w-100 text-center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>CALL US NOW</span>
                  </a>
                  <button type="button" class="btn-dm btn-lime w-100 btn-whatsapp-cta" data-wa-phone="<?= e($waNumber) ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    <span>CHAT ON WHATSAPP</span>
                  </button>
                </div>
              </div>

              <!-- Styled Map Card -->
              <div class="dm-card p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--dm-border) !important; background: var(--dm-surface);">
                  <div class="d-flex align-items-center gap-2">
                    <span class="beacon-dot"></span>
                    <span class="text-lime fw-bold" style="font-family: var(--font-mono); font-size: 0.76rem;">GPS LOCATION</span>
                  </div>
                  <span class="text-muted" style="font-family: var(--font-mono); font-size: 0.72rem;"><?= e($geoCoords) ?></span>
                </div>

                <div class="position-relative" style="height: 250px; background: #07090A; overflow: hidden;">
                  <iframe 
                    title="24HRS Fitness Location Map"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.25436351647!2d-74.11976373099557!3d40.69766374874431!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY!5e0!3m2!1sen!2sus!4v1714000000000!5m2!1sen!2sus"
                    width="100%" 
                    height="100%" 
                    style="border:0; filter: invert(90%) hue-rotate(180deg) contrast(1.1) brightness(0.85);" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                  </iframe>

                  <div style="position: absolute; bottom: 12px; left: 12px; background: rgba(6, 8, 11, 0.95); border: 1px solid var(--dm-lime); padding: 6px 12px; box-shadow: 0 0 15px rgba(0, 229, 255, 0.35);">
                    <div style="font-family: var(--font-display); font-size: 0.9rem; font-weight: 800; color: #fff;">24HRS FITNESS CLUB</div>
                    <div style="font-family: var(--font-mono); font-size: 0.68rem; color: var(--dm-lime);">MAIN RECEPTION & GYM FLOOR</div>
                  </div>
                </div>

                <div class="p-3" style="background: var(--dm-surface);">
                  <a href="https://maps.google.com/?q=<?= urlencode($siteAddress) ?>" target="_blank" rel="noopener noreferrer" class="btn-dm btn-metallic btn-sm w-100">
                    <span>OPEN IN GOOGLE MAPS</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                  </a>
                </div>
              </div>

            </div>
          </div>

          <!-- RIGHT COLUMN: INTERACTIVE MULTI-MODE FORM -->
          <div class="col-lg-7">
            <div class="dm-card p-4 p-md-5">
              
              <!-- Form Switcher Mode Tabs -->
              <div class="d-flex flex-wrap gap-2 mb-4">
                <button type="button" class="filter-tab contact-mode-tab active" data-mode="vip-pass">FREE 1-DAY PASS</button>
                <button type="button" class="filter-tab contact-mode-tab" data-mode="facility-tour">BOOK GYM TOUR</button>
                <button type="button" class="filter-tab contact-mode-tab" data-mode="general-inquiry">GENERAL INQUIRY</button>
              </div>

              <!-- Dynamic Form Header -->
              <div class="mb-4">
                <h2 id="contactFormHeading" class="card-title mb-1" style="font-size: 1.85rem;">CLAIM FREE 1-DAY WORKOUT PASS</h2>
                <p id="contactFormSubheading" class="text-muted" style="font-size: 0.92rem;">
                  Experience our gym floor, weights, cardio machines, and steam sauna for one workout session.
                </p>
              </div>

              <!-- Alert Banner for Submission Feedback -->
              <div id="contactFormAlert" class="form-alert-banner"></div>

              <!-- Main Interactive Form -->
              <form id="mainContactForm" novalidate>
                <?= Security::csrfField() ?>
                <!-- Anti-bot honeypot -->
                <input type="text" name="dm_telemetry_check" style="display:none !important;" tabindex="-1" autocomplete="off">
                <input type="hidden" id="inquiryType" name="inquiry_type" value="vip-pass">

                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label" for="contactName">Full Name <span class="req">*</span></label>
                      <input type="text" id="contactName" name="name" class="form-control-dm" placeholder="Your Full Name" required>
                      <div class="invalid-feedback-msg">Please enter your name.</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label" for="contactEmail">Email Address <span class="req">*</span></label>
                      <input type="email" id="contactEmail" name="email" class="form-control-dm" placeholder="name@example.com" required>
                      <div class="invalid-feedback-msg">Please enter a valid email address.</div>
                    </div>
                  </div>
                </div>

                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label" for="contactPhone">Phone Number <span class="req">*</span></label>
                      <input type="tel" id="contactPhone" name="phone" class="form-control-dm" placeholder="+91 99201 20785" required>
                      <div class="invalid-feedback-msg">Phone number required for confirmation SMS.</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label" for="contactLocation">Gym Branch <span class="req">*</span></label>
                      <select id="contactLocation" name="location" class="form-control-dm" required>
                        <option value="Main Branch — City Center">Main Branch — City Center</option>
                        <option value="West Branch — Commercial Hub">West Branch — Commercial Hub</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Dynamic Extra Fields -->
                <div id="dynamicExtraFields">
                  <div class="form-group">
                    <label class="form-label" for="preferredDate">Preferred Visit Date <span class="req">*</span></label>
                    <input type="date" id="preferredDate" name="preferred_date" class="form-control-dm" min="<?= date('Y-m-d') ?>" required>
                    <div class="invalid-feedback-msg">Please choose your intended visit date.</div>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label" for="contactMessage">Message / Fitness Goal <span class="text-muted">(Optional)</span></label>
                  <textarea id="contactMessage" name="message" class="form-control-dm" rows="3" placeholder="Tell us about your fitness goals (weight loss, muscle gain, stamina) or any questions..."></textarea>
                </div>

                <div class="form-group">
                  <label class="custom-checkbox-label">
                    <input type="checkbox" id="contactConsent" required>
                    <span>I confirm I am at least 18 years of age and agree to gym safety rules.</span>
                  </label>
                  <div class="invalid-feedback-msg">You must check this box to proceed.</div>
                </div>

                <button type="submit" class="btn-dm btn-lime btn-lg w-100 mt-2">
                  <span id="contactSubmitBtnText">GET FREE 1-DAY PASS</span>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
              </form>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main>

<?php
require_once __DIR__ . '/includes/modals.php';
require_once __DIR__ . '/includes/footer.php';
?>
