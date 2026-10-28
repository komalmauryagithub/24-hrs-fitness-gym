<?php
/**
 * DARK MATTER — Elite Fitness
 * Master Coaches & Physical Architects Management
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/uploader.php';
require_once dirname(__DIR__) . '/includes/Database.php';
require_once dirname(__DIR__) . '/includes/Security.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

use DarkMatter\Admin\Auth;
use DarkMatter\Admin\Uploader;
use DarkMatter\Database;
use DarkMatter\Security;

Auth::requireAuth();

$action = $_GET['action'] ?? 'list';
$trainerId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

$categories = [
    'strength'     => 'Strength & Hypertrophy',
    'conditioning' => 'Metabolic Systems & Endurance',
    'biomechanics' => 'Biomechanics & Combatives',
    'longevity'    => 'Physiology & Longevity'
];

// Handle State-Changing POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Operation aborted.');
        header('Location: trainers.php');
        exit;
    }

    $postAction = $_POST['action'] ?? '';

    // 1. Toggle Active Status
    if ($postAction === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $trainer = Database::fetchOne("SELECT `id`, `is_active`, `name` FROM `trainers` WHERE `id` = :id", [':id' => $id]);
            if ($trainer) {
                $newStatus = $trainer['is_active'] ? 0 : 1;
                Database::execute("UPDATE `trainers` SET `is_active` = :st WHERE `id` = :id", [
                    ':st' => $newStatus,
                    ':id' => $id
                ]);
                $word = $newStatus ? 'published' : 'unpublished';
                Auth::setFlash('success', "Coach '{$trainer['name']}' {$word} successfully.");
            }
        } catch (\Throwable $e) {
            error_log('[TRAINER TOGGLE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to update coach status.');
        }
        header('Location: trainers.php');
        exit;
    }

    // 2. Delete Coach
    if ($postAction === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            Database::execute("DELETE FROM `trainers` WHERE `id` = :id", [':id' => $id]);
            Auth::setFlash('success', 'Master Coach record permanently deleted.');
        } catch (\Throwable $e) {
            error_log('[TRAINER DELETE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to remove coach record.');
        }
        header('Location: trainers.php');
        exit;
    }

    // 3. Save Coach (Create or Edit)
    if ($postAction === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = Security::sanitizeString($_POST['name'] ?? '', 100);
        $slug = Security::sanitizeString($_POST['slug'] ?? '', 50);
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        }
        $roleTitle = Security::sanitizeString($_POST['role_title'] ?? '', 150);
        $specialties = Security::sanitizeString($_POST['specialties'] ?? '', 255);
        $category = Security::validateEnum($_POST['category'] ?? 'strength', array_keys($categories)) ?? 'strength';
        $bio = Security::sanitizeString($_POST['bio'] ?? '', 2000);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        // Credentials JSON
        $rawCredentials = trim((string) ($_POST['credentials'] ?? ''));
        $credentialsList = [];
        if ($rawCredentials !== '') {
            $lines = explode("\n", $rawCredentials);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    $credentialsList[] = $trimmed;
                }
            }
        }
        $credentialsJson = json_encode($credentialsList, JSON_UNESCAPED_UNICODE);

        // Handle Image: File Upload OR Direct URL
        $imageUrl = trim((string) ($_POST['image_url'] ?? ''));

        if (!empty($_FILES['image_file']['name'])) {
            $uploadedPath = Uploader::processUpload($_FILES['image_file'], 'trainers', $uploadError);
            if ($uploadedPath) {
                $imageUrl = $uploadedPath;
            } else {
                $error = 'Image Upload Failed: ' . $uploadError;
            }
        }

        if ($imageUrl === '') {
            $imageUrl = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80';
        }

        if ($name === '' || $roleTitle === '') {
            $error = 'Coach legal name and operational role title are required.';
        } elseif ($error === null) {
            try {
                // Check slug uniqueness
                $existing = Database::fetchOne(
                    "SELECT `id` FROM `trainers` WHERE `slug` = :slug AND `id` != :id",
                    [':slug' => $slug, ':id' => $id]
                );

                if ($existing) {
                    $error = "The slug identifier '{$slug}' is already allocated to another coach.";
                } else {
                    if ($id > 0) {
                        $sql = "UPDATE `trainers` SET
                                `slug` = :slug,
                                `name` = :name,
                                `role_title` = :role_title,
                                `specialties` = :specialties,
                                `category` = :category,
                                `credentials` = :credentials,
                                `bio` = :bio,
                                `image_url` = :image_url,
                                `sort_order` = :sort_order,
                                `is_active` = :is_active
                                WHERE `id` = :id";
                        Database::execute($sql, [
                            ':slug'        => $slug,
                            ':name'        => $name,
                            ':role_title'  => $roleTitle,
                            ':specialties' => $specialties,
                            ':category'    => $category,
                            ':credentials' => $credentialsJson,
                            ':bio'         => $bio,
                            ':image_url'   => $imageUrl,
                            ':sort_order'  => $sortOrder,
                            ':is_active'   => $isActive,
                            ':id'          => $id
                        ]);
                        Auth::setFlash('success', "Coach '{$name}' updated successfully.");
                    } else {
                        $sql = "INSERT INTO `trainers`
                                (`slug`, `name`, `role_title`, `specialties`, `category`, `credentials`, `bio`, `image_url`, `sort_order`, `is_active`)
                                VALUES
                                (:slug, :name, :role_title, :specialties, :category, :credentials, :bio, :image_url, :sort_order, :is_active)";
                        Database::execute($sql, [
                            ':slug'        => $slug,
                            ':name'        => $name,
                            ':role_title'  => $roleTitle,
                            ':specialties' => $specialties,
                            ':category'    => $category,
                            ':credentials' => $credentialsJson,
                            ':bio'         => $bio,
                            ':image_url'   => $imageUrl,
                            ':sort_order'  => $sortOrder,
                            ':is_active'   => $isActive
                        ]);
                        Auth::setFlash('success', "Trainer '{$name}' added successfully.");
                    }
                    header('Location: trainers.php');
                    exit;
                }
            } catch (\Throwable $e) {
                error_log('[TRAINER SAVE ERROR]: ' . $e->getMessage());
                $error = 'Database write failure: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Trainer for Edit View
$trainerData = null;
if ($action === 'edit' && $trainerId > 0) {
    try {
        $trainerData = Database::fetchOne("SELECT * FROM `trainers` WHERE `id` = :id", [':id' => $trainerId]);
        if (!$trainerData) {
            Auth::setFlash('danger', 'Requested trainer record not found.');
            header('Location: trainers.php');
            exit;
        }
    } catch (\Throwable $e) {
        error_log('[TRAINER FETCH ERROR]: ' . $e->getMessage());
    }
}

$pageTitle = 'Trainers & Coaches';
require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-toolbar">
  <div>
    <h1 class="adm-page-title" style="margin-bottom: 0;">TRAINERS & COACHES</h1>
    <p class="adm-page-subtitle">Add, edit, or remove personal trainers, certifications, specialties, and photos.</p>
  </div>
  <div>
    <?php if ($action === 'list'): ?>
      <a href="trainers.php?action=create" class="adm-btn adm-btn-lime adm-btn-sm">+ Add New Trainer</a>
    <?php else: ?>
      <a href="trainers.php" class="adm-btn adm-btn-outline adm-btn-sm">&larr; Back to Trainers List</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($error): ?>
  <div class="adm-alert adm-alert-danger">
    <div><?= e($error) ?></div>
    <button type="button" class="adm-alert-close" onclick="this.parentElement.remove();">&times;</button>
  </div>
<?php endif; ?>

<?php if ($action === 'create' || $action === 'edit'): ?>
  <!-- CREATE / EDIT FORM -->
  <div class="adm-card" style="max-width: 800px;">
    <h3 style="margin-bottom: 1.5rem; font-size: 1.3rem;">
      <?= $action === 'edit' ? 'EDIT TRAINER: ' . e($trainerData['name']) : 'ADD NEW TRAINER' ?>
    </h3>

    <form method="POST" action="trainers.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e($trainerData['id'] ?? 0) ?>">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="name">Trainer Full Name *</label>
          <input type="text" id="name" name="name" class="adm-input" required placeholder="e.g. Vikram Singh" value="<?= e($trainerData['name'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="slug">URL Slug (Optional)</label>
          <input type="text" id="slug" name="slug" class="adm-input" placeholder="e.g. vikram-singh" value="<?= e($trainerData['slug'] ?? '') ?>">
          <small style="color: var(--adm-silver-dim); font-size: 0.72rem;">Leave blank to auto-generate from name.</small>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="role_title">Role / Title *</label>
          <input type="text" id="role_title" name="role_title" class="adm-input" required placeholder="e.g. Head Strength & Conditioning Coach" value="<?= e($trainerData['role_title'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="category">Category</label>
          <select id="category" name="category" class="adm-select">
            <?php foreach ($categories as $catKey => $catLabel): ?>
              <option value="<?= e($catKey) ?>" <?= ($trainerData['category'] ?? '') === $catKey ? 'selected' : '' ?>>
                <?= e($catLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="margin-bottom: 1.25rem;">
        <label class="adm-label" for="specialties">Specialties & Core Focus</label>
        <input type="text" id="specialties" name="specialties" class="adm-input" placeholder="e.g. Muscle Building • Fat Loss • Functional Training" value="<?= e($trainerData['specialties'] ?? '') ?>">
      </div>

      <div style="margin-bottom: 1.25rem;">
        <label class="adm-label" for="bio">About Trainer / Bio</label>
        <textarea id="bio" name="bio" rows="4" class="adm-textarea" placeholder="Trainer background, years of experience, coaching style..."><?= e($trainerData['bio'] ?? '') ?></textarea>
      </div>

      <div style="margin-bottom: 1.25rem;">
        <label class="adm-label" for="credentials">Certifications & Experience (1 per line)</label>
        <?php
          $credLines = '';
          if (!empty($trainerData['credentials'])) {
              $decoded = is_string($trainerData['credentials']) ? json_decode($trainerData['credentials'], true) : $trainerData['credentials'];
              if (is_array($decoded)) {
                  $credLines = implode("\n", $decoded);
              }
          }
        ?>
        <textarea id="credentials" name="credentials" rows="4" class="adm-textarea" placeholder="Certified Fitness Trainer (ACE / ISSA)&#10;Certified Sports Nutritionist&#10;8+ Years Experience"><?= e($credLines) ?></textarea>
      </div>

      <!-- Image Source Selection -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: var(--adm-surface); padding: 1rem; border: 1px solid var(--adm-border);">
        <div>
          <label class="adm-label" for="image_file">Upload Trainer Photo (JPEG, PNG, WebP, max 5MB)</label>
          <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="adm-input" style="padding: 0.35rem 0.5rem;">
        </div>
        <div>
          <label class="adm-label" for="image_url">OR Existing Image URL / Asset Path</label>
          <input type="text" id="image_url" name="image_url" class="adm-input" placeholder="https://images.unsplash.com/... or assets/images/..." value="<?= e($trainerData['image_url'] ?? '') ?>">
          <?php if (!empty($trainerData['image_url'])): ?>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
              <img src="<?= (str_starts_with($trainerData['image_url'], 'http') ? '' : '../') . e($trainerData['image_url']) ?>" alt="Preview" class="adm-thumb">
              <span style="font-size: 0.72rem; color: var(--adm-silver-dim);">Current Photo</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
        <div>
          <label class="adm-label" for="sort_order">Display Order</label>
          <input type="number" id="sort_order" name="sort_order" class="adm-input" value="<?= e($trainerData['sort_order'] ?? 10) ?>">
        </div>
        <div style="display: flex; align-items: flex-end; padding-bottom: 0.5rem;">
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input type="checkbox" name="is_active" value="1" <?= (!isset($trainerData['is_active']) || !empty($trainerData['is_active'])) ? 'checked' : '' ?> style="accent-color: var(--adm-lime); width: 18px; height: 18px;">
            <span style="font-size: 0.88rem; color: var(--adm-white);">Visible on Gym Website</span>
          </label>
        </div>
      </div>

      <div style="display: flex; gap: 1rem;">
        <button type="submit" class="adm-btn adm-btn-lime">Save Trainer Profile</button>
        <a href="trainers.php" class="adm-btn adm-btn-outline">Cancel</a>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- LIST VIEW -->
  <?php
  try {
      $trainers = Database::fetchAll("SELECT * FROM `trainers` ORDER BY `sort_order` ASC, `id` ASC");
  } catch (\Throwable $e) {
      error_log('[TRAINERS LIST ERROR]: ' . $e->getMessage());
      $trainers = [];
  }
  ?>

  <?php if (empty($trainers)): ?>
    <div class="adm-card">
      <div class="adm-empty">
        <div class="adm-empty-title">No Trainers Found</div>
        <div class="adm-empty-text">No trainers are currently listed. Add your first trainer to display on the website.</div>
        <a href="trainers.php?action=create" class="adm-btn adm-btn-lime">+ Add First Trainer</a>
      </div>
    </div>
  <?php else: ?>
    <div class="adm-table-container">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Photo</th>
            <th>Name & Title</th>
            <th>Category</th>
            <th>Specialties</th>
            <th>Status</th>
            <th>Order</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($trainers as $t): ?>
            <tr>
              <td>
                <img 
                  src="<?= (str_starts_with($t['image_url'], 'http') ? '' : '../') . e($t['image_url']) ?>" 
                  alt="<?= e($t['name']) ?>" 
                  class="adm-thumb"
                  onerror="this.src='../assets/images/favicon.svg';"
                >
              </td>
              <td>
                <div style="font-weight: 700; font-size: 1.05rem; font-family: var(--adm-font-heading); color: var(--adm-white);">
                  <?= e($t['name']) ?>
                </div>
                <div style="font-size: 0.72rem; color: var(--adm-lime); font-family: var(--adm-font-mono);">
                  <?= e($t['role_title']) ?>
                </div>
              </td>
              <td>
                <span class="adm-badge scheduled">
                  <?= e($categories[$t['category']] ?? $t['category']) ?>
                </span>
              </td>
              <td style="font-size: 0.8rem; color: var(--adm-silver); max-width: 250px;">
                <?= e($t['specialties']) ?>
              </td>
              <td>
                <form method="POST" action="trainers.php" style="display: inline;">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= e($t['id']) ?>">
                  <button type="submit" class="adm-badge <?= $t['is_active'] ? 'active' : 'inactive' ?>" style="cursor: pointer; border-style: solid;" title="Click to toggle publish status">
                    <?= $t['is_active'] ? '&bull; PUBLISHED' : '&bull; HIDDEN' ?>
                  </button>
                </form>
              </td>
              <td style="font-family: var(--adm-font-mono); color: var(--adm-silver-dim);"><?= e($t['sort_order']) ?></td>
              <td style="text-align: right; white-space: nowrap;">
                <a href="trainers.php?action=edit&id=<?= e($t['id']) ?>" class="adm-btn adm-btn-outline adm-btn-sm" style="margin-right: 0.35rem;">Edit</a>
                <button type="button" class="adm-btn adm-btn-danger adm-btn-sm" onclick="openConfirmModal('trainers.php', <?= e($t['id']) ?>, 'Delete Trainer <?= e($t['name']) ?>', 'Are you sure you want to permanently delete trainer <?= e($t['name']) ?>? This cannot be undone.')">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
