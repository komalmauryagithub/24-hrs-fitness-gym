<?php
/**
 * DARK MATTER — Elite Fitness
 * Shared Footer Partial (PHP Component)
 */

declare(strict_types=1);

require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Database.php';

use DarkMatter\Security;
use DarkMatter\Database;

$csrfToken = Security::getCsrfToken();

// Attempt to fetch settings from database or use defaults
try {
    $settingsRows = Database::fetchAll("SELECT `setting_key`, `setting_value` FROM `site_settings`");
    $settings = array_column($settingsRows, 'setting_value', 'setting_key');
} catch (\Throwable $e) {
    $settings = [];
}

$siteAddress = $settings['address'] ?? 'Plot 42, Commercial Avenue, Opp. Metro Station, Andheri West, Mumbai, MH 400053';
$sitePhone = $settings['contact_phone'] ?? '+91 99201 20785';
$siteEmail = $settings['contact_email'] ?? 'contact@24hrsfitness.com';
$siteWhatsapp = $settings['whatsapp_number'] ?? '919920120785';
?>
  <!-- ================= SITE FOOTER ================= -->
  <footer class="site-footer">
    <div class="container-custom footer-top">
      <div class="row g-5">
        <div class="col-lg-4 col-md-6 footer-brand">
          <img src="assets/images/logo.png" alt="24HRS Fitness" width="240" class="mb-3" style="height: 70px; width: auto; object-fit: contain; filter: drop-shadow(0 0 20px rgba(0, 229, 255, 0.55));">
          <p>
            A premium 24/7 fitness club featuring world-class workout equipment, certified personal trainers, steam & sauna, and flexible membership plans.
          </p>
          <div class="mt-4">
            <div class="status-beacon">
              <span class="beacon-dot"></span>
              <span>GYM ACCESS: OPEN 24/7 (365 DAYS)</span>
            </div>
          </div>
        </div>

        <div class="col-lg-2 col-md-6">
          <h4 class="footer-heading">QUICK LINKS</h4>
          <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="programs.php">Workout Programs</a></li>
            <li><a href="membership.php">Membership Plans</a></li>
            <li><a href="trainers.php">Trainers & Coaches</a></li>
            <li><a href="about.php">About & Gallery</a></li>
            <li><a href="contact.php">Contact Us</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6">
          <h4 class="footer-heading">OUR LOCATION</h4>
          <div class="footer-contact-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span><?= e($siteAddress) ?></span>
          </div>
          <div class="footer-contact-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <span>Reception Desk: <?= e($sitePhone) ?></span>
          </div>
          <div class="footer-contact-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Front Desk: 06:00 AM – 10:00 PM Daily<br>Member Access: Open 24/7 Non-Stop</span>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <h4 class="footer-heading">NEWSLETTER</h4>
          <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 1rem;">
            Subscribe to get workout tips, fitness updates, and exclusive membership offers.
          </p>
          <form class="newsletter-form" novalidate>
            <?= Security::csrfField() ?>
            <!-- Honeypot Anti-Spam Field -->
            <input type="text" name="dm_telemetry_check" style="display:none !important;" tabindex="-1" autocomplete="off">
            <input type="hidden" name="inquiry_type" value="newsletter">
            <div class="d-flex flex-column gap-2">
              <input type="email" name="email" class="form-control-dm" placeholder="Enter your email address" required aria-label="Email address for dispatch">
              <button type="submit" class="btn-dm btn-lime btn-sm">
                <span>SUBSCRIBE NOW</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="container-custom">
      <div class="footer-bottom">
        <div>
          &copy; <?= date('Y') ?> 24HRS FITNESS. ALL RIGHTS RESERVED.
        </div>
        <div class="d-flex gap-4">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms & Conditions</a>
          <a href="#">Gym Rules</a>
          <a href="admin/login.php" title="Admin Portal" style="color: var(--dm-silver);"><span style="color: var(--dm-lime);">&bull;</span> Admin Login</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- ================= FLOATING QUICK ACTION BAR (CALL NOW & WHATSAPP) ================= -->
  <aside class="floating-quick-dock" aria-label="Quick Concierge Access">
    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $sitePhone)) ?>" class="dock-btn dock-btn-call" title="Call Us Now">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
      <span class="dock-label">Call Now</span>
    </a>
    <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $siteWhatsapp)) ?>?text=<?= urlencode('Hello, I would like to inquire about membership plans at 24HRS Fitness.') ?>" target="_blank" rel="noopener noreferrer" class="dock-btn dock-btn-wa" title="WhatsApp Chat">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.78 14.12c-.24.68-1.39 1.3-1.92 1.35-.49.05-1.12.07-3.64-.98-3.07-1.27-5.06-4.4-5.21-4.6-.15-.2-1.25-1.66-1.25-3.17 0-1.51.79-2.25 1.07-2.55.28-.3.62-.37.82-.37.21 0 .41.01.59.02.19.01.44-.07.69.52.25.6.87 2.11.95 2.26.08.16.13.34.03.55-.11.21-.16.34-.32.53-.16.19-.34.42-.48.57-.16.16-.33.33-.14.65.19.32.84 1.38 1.8 2.24 1.24 1.11 2.28 1.45 2.6 1.61.32.16.51.13.7-.08.19-.22.82-.96 1.04-1.28.22-.33.44-.27.74-.16.3.11 1.91.9 2.24 1.06.33.16.55.25.63.38.08.14.08.79-.16 1.47z"/></svg>
      <span class="dock-label">WhatsApp</span>
    </a>
  </aside>

  <!-- Toast Notification Container Placeholder -->
  <div class="dm-toast-container" aria-live="polite"></div>

  <!-- Core Scripts -->
  <script src="js/main.js"></script>
  <script src="js/forms.js"></script>
  <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
    <?php foreach ($extraScripts as $script): ?>
      <script src="<?= e($script) ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
