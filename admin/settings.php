<?php
/**
 * DARK MATTER — Elite Fitness
 * Website Telemetry & Facility Settings Management
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/Database.php';
require_once dirname(__DIR__) . '/includes/Security.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

use DarkMatter\Admin\Auth;
use DarkMatter\Database;
use DarkMatter\Security;

Auth::requireAuth();

$error = null;

// Handle Form Submission (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Settings update cancelled.');
        header('Location: settings.php');
        exit;
    }

    $settingsInput = $_POST['settings'] ?? [];
    if (!is_array($settingsInput)) {
        $error = 'Invalid settings payload submitted.';
    } else {
        try {
            $sql = "INSERT INTO `site_settings` (`setting_key`, `setting_value`, `description`)
                    VALUES (:k, :v, :d)
                    ON DUPLICATE KEY UPDATE `setting_value` = :v2";

            foreach ($settingsInput as $key => $val) {
                $cleanKey = Security::sanitizeString($key, 100);
                $cleanVal = trim((string) $val);

                Database::execute($sql, [
                    ':k'  => $cleanKey,
                    ':v'  => $cleanVal,
                    ':d'  => 'Configuration parameter: ' . $cleanKey,
                    ':v2' => $cleanVal
                ]);
            }

            Auth::setFlash('success', 'Gym settings updated successfully.');
            header('Location: settings.php');
            exit;
        } catch (\Throwable $e) {
            error_log('[SETTINGS UPDATE ERROR]: ' . $e->getMessage());
            $error = 'Database write failed: ' . $e->getMessage();
        }
    }
}

// Fetch All Current Settings into Key-Value Map
$settings = [];
try {
    $rows = Database::fetchAll("SELECT `setting_key`, `setting_value` FROM `site_settings`");
    foreach ($rows as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
} catch (\Throwable $e) {
    error_log('[SETTINGS FETCH ERROR]: ' . $e->getMessage());
}

$pageTitle = 'Gym & Website Settings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-toolbar">
  <div>
    <h1 class="adm-page-title" style="margin-bottom: 0;">GYM & WEBSITE SETTINGS</h1>
    <p class="adm-page-subtitle">Update your gym name, contact information, physical address, opening hours, and social media links.</p>
  </div>
</div>

<?php if ($error): ?>
  <div class="adm-alert adm-alert-danger">
    <div><?= e($error) ?></div>
    <button type="button" class="adm-alert-close" onclick="this.parentElement.remove();">&times;</button>
  </div>
<?php endif; ?>

<form method="POST" action="settings.php" style="max-width: 900px;">
  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

  <!-- Section 1: Brand & Facility Scale -->
  <div class="adm-card" style="margin-bottom: 1.5rem;">
    <h3 style="font-size: 1.15rem; margin-bottom: 1rem; color: var(--adm-lime); display: flex; align-items: center; gap: 0.5rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
      <span>1. GYM DETAILS & BRAND</span>
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
      <div>
        <label class="adm-label" for="site_name">Gym Name</label>
        <input 
          type="text" 
          id="site_name" 
          name="settings[site_name]" 
          class="adm-input" 
          value="<?= e($settings['site_name'] ?? '24HRS FITNESS') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="site_tagline">Gym Tagline / Headline</label>
        <input 
          type="text" 
          id="site_tagline" 
          name="settings[site_tagline]" 
          class="adm-input" 
          value="<?= e($settings['site_tagline'] ?? 'Reach Your Peak Fitness') ?>"
        >
      </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
      <div>
        <label class="adm-label" for="facility_sq_ft">Gym Floor Area</label>
        <input 
          type="text" 
          id="facility_sq_ft" 
          name="settings[facility_sq_ft]" 
          class="adm-input" 
          value="<?= e($settings['facility_sq_ft'] ?? '25,000 sq.ft') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="active_member_cap">Max Member Capacity</label>
        <input 
          type="number" 
          id="active_member_cap" 
          name="settings[active_member_cap]" 
          class="adm-input" 
          value="<?= e($settings['active_member_cap'] ?? '500') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="current_active_members">Active Members Shown</label>
        <input 
          type="number" 
          id="current_active_members" 
          name="settings[current_active_members]" 
          class="adm-input" 
          value="<?= e($settings['current_active_members'] ?? '460') ?>"
        >
      </div>
    </div>
  </div>

  <!-- Section 2: Contact & Concierge Telemetry -->
  <div class="adm-card" style="margin-bottom: 1.5rem;">
    <h3 style="font-size: 1.15rem; margin-bottom: 1rem; color: var(--adm-lime); display: flex; align-items: center; gap: 0.5rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
      <span>2. CONTACT DETAILS & ADDRESS</span>
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
      <div>
        <label class="adm-label" for="contact_phone">Phone Number (Call Now)</label>
        <input 
          type="text" 
          id="contact_phone" 
          name="settings[contact_phone]" 
          class="adm-input" 
          value="<?= e($settings['contact_phone'] ?? '+91 99201 20785') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="contact_email">Email Address</label>
        <input 
          type="email" 
          id="contact_email" 
          name="settings[contact_email]" 
          class="adm-input" 
          value="<?= e($settings['contact_email'] ?? 'contact@24hrsfitness.com') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="whatsapp_number">WhatsApp Number (with country code, digits only)</label>
        <input 
          type="text" 
          id="whatsapp_number" 
          name="settings[whatsapp_number]" 
          class="adm-input" 
          placeholder="e.g. 919920120785"
          value="<?= e($settings['whatsapp_number'] ?? '919920120785') ?>"
        >
      </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
      <div style="flex: 2;">
        <label class="adm-label" for="address">Gym Address</label>
        <input 
          type="text" 
          id="address" 
          name="settings[address]" 
          class="adm-input" 
          value="<?= e($settings['address'] ?? '400 Obsidian Boulevard, District 7, Metropolis, NY 10001') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="geo_coordinates">Coordinates / Landmark</label>
        <input 
          type="text" 
          id="geo_coordinates" 
          name="settings[geo_coordinates]" 
          class="adm-input" 
          value="<?= e($settings['geo_coordinates'] ?? '40.7128° N, 74.0060° W') ?>"
        >
      </div>
    </div>
  </div>

  <!-- Section 3: Operational Schedules -->
  <div class="adm-card" style="margin-bottom: 1.5rem;">
    <h3 style="font-size: 1.15rem; margin-bottom: 1rem; color: var(--adm-lime); display: flex; align-items: center; gap: 0.5rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      <span>3. OPENING HOURS</span>
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
      <div>
        <label class="adm-label" for="operating_hours_members">Gym Floor Access Hours</label>
        <input 
          type="text" 
          id="operating_hours_members" 
          name="settings[operating_hours_members]" 
          class="adm-input" 
          value="<?= e($settings['operating_hours_members'] ?? '24/7 Access (Open 365 Days)') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="operating_hours_staff">Reception & Front Desk Hours</label>
        <input 
          type="text" 
          id="operating_hours_staff" 
          name="settings[operating_hours_staff]" 
          class="adm-input" 
          value="<?= e($settings['operating_hours_staff'] ?? '06:00 AM – 10:00 PM Daily') ?>"
        >
      </div>
    </div>
  </div>

  <!-- Section 4: Social Transmission Channels -->
  <div class="adm-card" style="margin-bottom: 1.75rem;">
    <h3 style="font-size: 1.15rem; margin-bottom: 1rem; color: var(--adm-lime); display: flex; align-items: center; gap: 0.5rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
      <span>4. SOCIAL MEDIA LINKS</span>
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
      <div>
        <label class="adm-label" for="social_instagram">Instagram Profile URL</label>
        <input 
          type="text" 
          id="social_instagram" 
          name="settings[social_instagram]" 
          class="adm-input" 
          placeholder="https://instagram.com/yourgym"
          value="<?= e($settings['social_instagram'] ?? '') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="social_twitter">Facebook / Twitter URL</label>
        <input 
          type="text" 
          id="social_twitter" 
          name="settings[social_twitter]" 
          class="adm-input" 
          placeholder="https://facebook.com/yourgym"
          value="<?= e($settings['social_twitter'] ?? '') ?>"
        >
      </div>
      <div>
        <label class="adm-label" for="social_youtube">YouTube Channel URL</label>
        <input 
          type="text" 
          id="social_youtube" 
          name="settings[social_youtube]" 
          class="adm-input" 
          placeholder="https://youtube.com/@yourgym"
          value="<?= e($settings['social_youtube'] ?? '') ?>"
        >
      </div>
    </div>
  </div>

  <div>
    <button type="submit" class="adm-btn adm-btn-lime">Save All Settings</button>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
