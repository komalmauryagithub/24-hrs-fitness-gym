<?php
/**
 * DARK MATTER — Elite Fitness
 * Training Protocols & Facilities Management
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
$programId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

$categories = [
    'strength'     => 'Strength & Hypertrophy',
    'conditioning' => 'Metabolic Systems & Conditioning',
    'recovery'     => 'Bio-Regeneration & Recovery',
    'functional'   => 'Functional Kinetics & Combatives'
];

// Handle State-Changing POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Operation aborted.');
        header('Location: programs.php');
        exit;
    }

    $postAction = $_POST['action'] ?? '';

    // 1. Toggle Active Status
    if ($postAction === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $prog = Database::fetchOne("SELECT `id`, `is_active`, `name` FROM `programs` WHERE `id` = :id", [':id' => $id]);
            if ($prog) {
                $newStatus = $prog['is_active'] ? 0 : 1;
                Database::execute("UPDATE `programs` SET `is_active` = :st WHERE `id` = :id", [
                    ':st' => $newStatus,
                    ':id' => $id
                ]);
                $word = $newStatus ? 'published' : 'unpublished';
                Auth::setFlash('success', "Protocol '{$prog['name']}' {$word} successfully.");
            }
        } catch (\Throwable $e) {
            error_log('[PROGRAM TOGGLE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to toggle protocol status.');
        }
        header('Location: programs.php');
        exit;
    }

    // 2. Delete Program
    if ($postAction === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            Database::execute("DELETE FROM `programs` WHERE `id` = :id", [':id' => $id]);
            Auth::setFlash('success', 'Training protocol permanently removed.');
        } catch (\Throwable $e) {
            error_log('[PROGRAM DELETE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to remove training protocol.');
        }
        header('Location: programs.php');
        exit;
    }

    // 3. Save Program (Create or Edit)
    if ($postAction === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = Security::sanitizeString($_POST['name'] ?? '', 150);
        $slug = Security::sanitizeString($_POST['slug'] ?? '', 50);
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        }
        $category = Security::validateEnum($_POST['category'] ?? 'strength', array_keys($categories)) ?? 'strength';
        $badgeText = Security::sanitizeString($_POST['badge_text'] ?? 'PROTOCOL SPECIFICATION', 100);
        $shortDesc = Security::sanitizeString($_POST['short_desc'] ?? '', 2000);
        $intensityScore = (float) ($_POST['intensity_score'] ?? 4.5);
        $intensityDisplay = Security::sanitizeString($_POST['intensity_display'] ?? "{$intensityScore} / 5.0 — High Output", 50);
        $durationMinutes = (int) ($_POST['duration_minutes'] ?? 60);
        $burnCaloriesRange = Security::sanitizeString($_POST['burn_calories_range'] ?? '600–900 kcal', 50);
        $coachRatio = Security::sanitizeString($_POST['coach_ratio'] ?? '1:6 Master Ratio', 50);
        $targetAdaptation = Security::sanitizeString($_POST['target_adaptation'] ?? '', 1000);
        $gearStations = Security::sanitizeString($_POST['gear_stations'] ?? '', 1000);
        $recommendedCadence = Security::sanitizeString($_POST['recommended_cadence'] ?? '3–4 Sessions Weekly', 255);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        // Image Handling
        $imageUrl = trim((string) ($_POST['image_url'] ?? ''));

        if (!empty($_FILES['image_file']['name'])) {
            $uploadedPath = Uploader::processUpload($_FILES['image_file'], 'programs', $uploadError);
            if ($uploadedPath) {
                $imageUrl = $uploadedPath;
            } else {
                $error = 'Image Upload Failed: ' . $uploadError;
            }
        }

        if ($imageUrl === '') {
            $imageUrl = 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=1200&q=80';
        }

        if ($name === '') {
            $error = 'Training protocol name is required.';
        } elseif ($error === null) {
            try {
                // Check slug uniqueness
                $existing = Database::fetchOne(
                    "SELECT `id` FROM `programs` WHERE `slug` = :slug AND `id` != :id",
                    [':slug' => $slug, ':id' => $id]
                );

                if ($existing) {
                    $error = "The slug identifier '{$slug}' is already used by another protocol.";
                } else {
                    if ($id > 0) {
                        $sql = "UPDATE `programs` SET
                                `slug` = :slug,
                                `name` = :name,
                                `category` = :category,
                                `badge_text` = :badge_text,
                                `short_desc` = :short_desc,
                                `intensity_score` = :intensity_score,
                                `intensity_display` = :intensity_display,
                                `duration_minutes` = :duration_minutes,
                                `burn_calories_range` = :burn_calories_range,
                                `coach_ratio` = :coach_ratio,
                                `target_adaptation` = :target_adaptation,
                                `gear_stations` = :gear_stations,
                                `recommended_cadence` = :recommended_cadence,
                                `image_url` = :image_url,
                                `sort_order` = :sort_order,
                                `is_active` = :is_active
                                WHERE `id` = :id";
                        Database::execute($sql, [
                            ':slug'                => $slug,
                            ':name'                => $name,
                            ':category'            => $category,
                            ':badge_text'          => $badgeText,
                            ':short_desc'          => $shortDesc,
                            ':intensity_score'     => $intensityScore,
                            ':intensity_display'   => $intensityDisplay,
                            ':duration_minutes'    => $durationMinutes,
                            ':burn_calories_range' => $burnCaloriesRange,
                            ':coach_ratio'         => $coachRatio,
                            ':target_adaptation'   => $targetAdaptation,
                            ':gear_stations'       => $gearStations,
                            ':recommended_cadence' => $recommendedCadence,
                            ':image_url'           => $imageUrl,
                            ':sort_order'          => $sortOrder,
                            ':is_active'           => $isActive,
                            ':id'                  => $id
                        ]);
                        Auth::setFlash('success', "Protocol '{$name}' updated successfully.");
                    } else {
                        $sql = "INSERT INTO `programs`
                                (`slug`, `name`, `category`, `badge_text`, `short_desc`, `intensity_score`, `intensity_display`, `duration_minutes`, `burn_calories_range`, `coach_ratio`, `target_adaptation`, `gear_stations`, `recommended_cadence`, `image_url`, `sort_order`, `is_active`)
                                VALUES
                                (:slug, :name, :category, :badge_text, :short_desc, :intensity_score, :intensity_display, :duration_minutes, :burn_calories_range, :coach_ratio, :target_adaptation, :gear_stations, :recommended_cadence, :image_url, :sort_order, :is_active)";
                        Database::execute($sql, [
                            ':slug'                => $slug,
                            ':name'                => $name,
                            ':category'            => $category,
                            ':badge_text'          => $badgeText,
                            ':short_desc'          => $shortDesc,
                            ':intensity_score'     => $intensityScore,
                            ':intensity_display'   => $intensityDisplay,
                            ':duration_minutes'    => $durationMinutes,
                            ':burn_calories_range' => $burnCaloriesRange,
                            ':coach_ratio'         => $coachRatio,
                            ':target_adaptation'   => $targetAdaptation,
                            ':gear_stations'       => $gearStations,
                            ':recommended_cadence' => $recommendedCadence,
                            ':image_url'           => $imageUrl,
                            ':sort_order'          => $sortOrder,
                            ':is_active'           => $isActive
                        ]);
                        Auth::setFlash('success', "New Training Protocol '{$name}' registered.");
                    }
                    header('Location: programs.php');
                    exit;
                }
            } catch (\Throwable $e) {
                error_log('[PROGRAM SAVE ERROR]: ' . $e->getMessage());
                $error = 'Database write failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Program for Edit
$programData = null;
if ($action === 'edit' && $programId > 0) {
    try {
        $programData = Database::fetchOne("SELECT * FROM `programs` WHERE `id` = :id", [':id' => $programId]);
        if (!$programData) {
            Auth::setFlash('danger', 'Requested protocol record not found.');
            header('Location: programs.php');
            exit;
        }
    } catch (\Throwable $e) {
        error_log('[PROGRAM FETCH ERROR]: ' . $e->getMessage());
    }
}

$pageTitle = 'Workout Programs';
require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-toolbar">
  <div>
    <h1 class="adm-page-title" style="margin-bottom: 0;">WORKOUT PROGRAMS</h1>
    <p class="adm-page-subtitle">Add, edit, or remove gym workout programs, training categories, duration, and equipment.</p>
  </div>
  <div>
    <?php if ($action === 'list'): ?>
      <a href="programs.php?action=create" class="adm-btn adm-btn-lime adm-btn-sm">+ Add New Program</a>
    <?php else: ?>
      <a href="programs.php" class="adm-btn adm-btn-outline adm-btn-sm">&larr; Back to Programs List</a>
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
      <?= $action === 'edit' ? 'EDIT PROGRAM: ' . e($programData['name']) : 'ADD NEW WORKOUT PROGRAM' ?>
    </h3>

    <form method="POST" action="programs.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e($programData['id'] ?? 0) ?>">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="name">Program Name *</label>
          <input type="text" id="name" name="name" class="adm-input" required placeholder="e.g. Strength & Muscle Hypertrophy" value="<?= e($programData['name'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="slug">URL Slug (Optional)</label>
          <input type="text" id="slug" name="slug" class="adm-input" placeholder="e.g. strength-hypertrophy" value="<?= e($programData['slug'] ?? '') ?>">
          <small style="color: var(--adm-silver-dim); font-size: 0.72rem;">Leave blank to auto-generate from name.</small>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="category">Category</label>
          <select id="category" name="category" class="adm-select">
            <?php foreach ($categories as $catKey => $catLabel): ?>
              <option value="<?= e($catKey) ?>" <?= ($programData['category'] ?? '') === $catKey ? 'selected' : '' ?>>
                <?= e($catLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="adm-label" for="badge_text">Tag / Badge</label>
          <input type="text" id="badge_text" name="badge_text" class="adm-input" placeholder="e.g. Heavy Strength, HIIT Cardio" value="<?= e($programData['badge_text'] ?? '') ?>">
        </div>
      </div>

      <div style="margin-bottom: 1.25rem;">
        <label class="adm-label" for="short_desc">Program Description</label>
        <textarea id="short_desc" name="short_desc" rows="3" class="adm-textarea" placeholder="Detailed description of workout structure and targets..."><?= e($programData['short_desc'] ?? '') ?></textarea>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="intensity_score">Intensity Score (1.0–5.0)</label>
          <input type="number" step="0.1" min="1.0" max="5.0" id="intensity_score" name="intensity_score" class="adm-input" value="<?= e($programData['intensity_score'] ?? 4.5) ?>">
        </div>
        <div>
          <label class="adm-label" for="intensity_display">Intensity Label</label>
          <input type="text" id="intensity_display" name="intensity_display" class="adm-input" placeholder="e.g. High Intensity • Advanced" value="<?= e($programData['intensity_display'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="duration_minutes">Duration (Minutes)</label>
          <input type="number" id="duration_minutes" name="duration_minutes" class="adm-input" value="<?= e($programData['duration_minutes'] ?? 60) ?>">
        </div>
        <div>
          <label class="adm-label" for="burn_calories_range">Calorie Burn Range</label>
          <input type="text" id="burn_calories_range" name="burn_calories_range" class="adm-input" placeholder="e.g. 500–800 kcal" value="<?= e($programData['burn_calories_range'] ?? '500–800 kcal') ?>">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="coach_ratio">Trainer to Member Ratio</label>
          <input type="text" id="coach_ratio" name="coach_ratio" class="adm-input" placeholder="e.g. 1:1 Personal or 1:10 Group" value="<?= e($programData['coach_ratio'] ?? '1:6 Group') ?>">
        </div>
        <div>
          <label class="adm-label" for="recommended_cadence">Recommended Schedule</label>
          <input type="text" id="recommended_cadence" name="recommended_cadence" class="adm-input" placeholder="e.g. 3–4 Days Weekly" value="<?= e($programData['recommended_cadence'] ?? '3–4 Days Weekly') ?>">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="target_adaptation">Fitness Goals & Benefits</label>
          <textarea id="target_adaptation" name="target_adaptation" rows="3" class="adm-textarea" placeholder="Muscle growth, strength gain, functional power..."><?= e($programData['target_adaptation'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="adm-label" for="gear_stations">Equipment & Facilities Used</label>
          <textarea id="gear_stations" name="gear_stations" rows="3" class="adm-textarea" placeholder="Olympic barbells, squat racks, dumbbells, cable machines..."><?= e($programData['gear_stations'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Image Source Selection -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: var(--adm-surface); padding: 1rem; border: 1px solid var(--adm-border);">
        <div>
          <label class="adm-label" for="image_file">Upload Program Photo (JPEG, PNG, WebP, max 5MB)</label>
          <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="adm-input" style="padding: 0.35rem 0.5rem;">
        </div>
        <div>
          <label class="adm-label" for="image_url">OR Existing Image URL</label>
          <input type="text" id="image_url" name="image_url" class="adm-input" placeholder="https://images.unsplash.com/... or assets/images/..." value="<?= e($programData['image_url'] ?? '') ?>">
          <?php if (!empty($programData['image_url'])): ?>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
              <img src="<?= (str_starts_with($programData['image_url'], 'http') ? '' : '../') . e($programData['image_url']) ?>" alt="Preview" class="adm-thumb">
              <span style="font-size: 0.72rem; color: var(--adm-silver-dim);">Current Image</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
        <div>
          <label class="adm-label" for="sort_order">Display Order</label>
          <input type="number" id="sort_order" name="sort_order" class="adm-input" value="<?= e($programData['sort_order'] ?? 10) ?>">
        </div>
        <div style="display: flex; align-items: flex-end; padding-bottom: 0.5rem;">
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input type="checkbox" name="is_active" value="1" <?= (!isset($programData['is_active']) || !empty($programData['is_active'])) ? 'checked' : '' ?> style="accent-color: var(--adm-lime); width: 18px; height: 18px;">
            <span style="font-size: 0.88rem; color: var(--adm-white);">Program Active (Show on gym website)</span>
          </label>
        </div>
      </div>

      <div style="display: flex; gap: 1rem;">
        <button type="submit" class="adm-btn adm-btn-lime">Save Workout Program</button>
        <a href="programs.php" class="adm-btn adm-btn-outline">Cancel</a>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- LIST VIEW -->
  <?php
  try {
      $programs = Database::fetchAll("SELECT * FROM `programs` ORDER BY `sort_order` ASC, `id` ASC");
  } catch (\Throwable $e) {
      error_log('[PROGRAMS LIST ERROR]: ' . $e->getMessage());
      $programs = [];
  }
  ?>

  <?php if (empty($programs)): ?>
    <div class="adm-card">
      <div class="adm-empty">
        <div class="adm-empty-title">No Workout Programs Found</div>
        <div class="adm-empty-text">No workout programs have been created yet. Add your first program.</div>
        <a href="programs.php?action=create" class="adm-btn adm-btn-lime">+ Add First Program</a>
      </div>
    </div>
  <?php else: ?>
    <div class="adm-table-container">
      <table class="adm-table adm-table-programs">
        <thead>
          <tr>
            <th>Photo</th>
            <th>Program Name</th>
            <th>Category</th>
            <th>Intensity</th>
            <th>Duration</th>
            <th>Calorie Burn</th>
            <th>Status</th>
            <th>Order</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($programs as $p): ?>
            <tr>
              <td>
                <img 
                  src="<?= (str_starts_with($p['image_url'], 'http') ? '' : '../') . e($p['image_url']) ?>" 
                  alt="<?= e($p['name']) ?>" 
                  class="adm-thumb"
                  onerror="this.src='../assets/images/favicon.svg';"
                >
              </td>
              <td>
                <div style="font-weight: 700; font-size: 1.05rem; font-family: var(--adm-font-heading); color: var(--adm-white);">
                  <?= e($p['name']) ?>
                </div>
                <div style="font-size: 0.72rem; color: var(--adm-silver-dim); font-family: var(--adm-font-mono);">
                  <?= e($p['badge_text']) ?>
                </div>
              </td>
              <td>
                <span class="adm-badge scheduled">
                  <?= e($categories[$p['category']] ?? $p['category']) ?>
                </span>
              </td>
              <td>
                <span style="font-family: var(--adm-font-mono); font-weight: 700; color: var(--adm-lime);">
                  <?= number_format((float)$p['intensity_score'], 1) ?> / 5.0
                </span>
              </td>
              <td style="font-family: var(--adm-font-mono); color: var(--adm-silver);">
                <?= e($p['duration_minutes']) ?> min
              </td>
              <td style="font-family: var(--adm-font-mono); font-size: 0.8rem; color: var(--adm-silver-dim);">
                <?= e($p['burn_calories_range']) ?>
              </td>
              <td>
                <form method="POST" action="programs.php" style="display: inline;">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                  <button type="submit" class="adm-badge <?= $p['is_active'] ? 'active' : 'inactive' ?>" style="cursor: pointer; border-style: solid;" title="Click to toggle publish status">
                    <?= $p['is_active'] ? '&bull; ACTIVE' : '&bull; HIDDEN' ?>
                  </button>
                </form>
              </td>
              <td style="font-family: var(--adm-font-mono); color: var(--adm-silver-dim);"><?= e($p['sort_order']) ?></td>
              <td style="text-align: right; white-space: nowrap;">
                <a href="programs.php?action=edit&id=<?= e($p['id']) ?>" class="adm-btn adm-btn-outline adm-btn-sm" style="margin-right: 0.35rem;">Edit</a>
                <button type="button" class="adm-btn adm-btn-danger adm-btn-sm" onclick="openConfirmModal('programs.php', <?= e($p['id']) ?>, 'Delete Program <?= e($p['name']) ?>', 'Are you sure you want to permanently delete program <?= e($p['name']) ?>? This cannot be undone.')">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
