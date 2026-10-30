<?php
/**
 * DARK MATTER — Elite Fitness
 * Spatial Gallery & Facility Imagery Management
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
$imageId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

$categories = [
    'strength'     => 'Strength & Weights Area',
    'conditioning' => 'Cardio & Crossfit Zone',
    'recovery'     => 'Steam, Sauna & Recovery',
    'combatives'   => 'Boxing & Combat Zone',
    'facility'     => 'Gym Floor & Reception'
];

// Handle State-Changing POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Operation aborted.');
        header('Location: gallery.php');
        exit;
    }

    $postAction = $_POST['action'] ?? '';

    // 1. Toggle Active Status
    if ($postAction === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $img = Database::fetchOne("SELECT `id`, `is_active`, `title` FROM `gallery_images` WHERE `id` = :id", [':id' => $id]);
            if ($img) {
                $newStatus = $img['is_active'] ? 0 : 1;
                Database::execute("UPDATE `gallery_images` SET `is_active` = :st WHERE `id` = :id", [
                    ':st' => $newStatus,
                    ':id' => $id
                ]);
                $word = $newStatus ? 'activated' : 'hidden';
                Auth::setFlash('success', "Image '{$img['title']}' {$word} successfully.");
            }
        } catch (\Throwable $e) {
            error_log('[GALLERY TOGGLE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to toggle image status.');
        }
        header('Location: gallery.php');
        exit;
    }

    // 2. Delete Image
    if ($postAction === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $img = Database::fetchOne("SELECT `id`, `image_url` FROM `gallery_images` WHERE `id` = :id", [':id' => $id]);
            if ($img) {
                // If local upload, safely remove file from disk
                if (str_starts_with($img['image_url'], 'uploads/')) {
                    $localPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $img['image_url'];
                    if (is_file($localPath)) {
                        @unlink($localPath);
                    }
                }
                Database::execute("DELETE FROM `gallery_images` WHERE `id` = :id", [':id' => $id]);
                Auth::setFlash('success', 'Gallery image permanently removed.');
            }
        } catch (\Throwable $e) {
            error_log('[GALLERY DELETE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to remove gallery image.');
        }
        header('Location: gallery.php');
        exit;
    }

    // 3. Save Image (Create or Edit)
    if ($postAction === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = Security::sanitizeString($_POST['title'] ?? '', 150);
        $category = Security::validateEnum($_POST['category'] ?? 'strength', array_keys($categories)) ?? 'strength';
        $tagLabel = Security::sanitizeString($_POST['tag_label'] ?? 'GYM AREA', 100);
        $altText = Security::sanitizeString($_POST['alt_text'] ?? $title, 255);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        // Image Handling (File Upload OR Safe URL)
        $imageUrl = trim((string) ($_POST['image_url'] ?? ''));

        if (!empty($_FILES['image_file']['name'])) {
            $uploadedPath = Uploader::processUpload($_FILES['image_file'], 'gallery', $uploadError);
            if ($uploadedPath) {
                $imageUrl = $uploadedPath;
            } else {
                $error = 'Image Upload Failed: ' . $uploadError;
            }
        }

        if ($title === '') {
            $error = 'Image title is required.';
        } elseif ($imageUrl === '') {
            $error = 'Please provide an image file upload or a valid image URL.';
        } elseif (!Uploader::validateImageUrl($imageUrl)) {
            $error = 'The image path or URL provided failed safety validation.';
        } elseif ($error === null) {
            try {
                if ($id > 0) {
                    $sql = "UPDATE `gallery_images` SET
                            `title` = :title,
                            `category` = :category,
                            `tag_label` = :tag_label,
                            `image_url` = :image_url,
                            `alt_text` = :alt_text,
                            `sort_order` = :sort_order,
                            `is_active` = :is_active
                            WHERE `id` = :id";
                    Database::execute($sql, [
                        ':title'      => $title,
                        ':category'   => $category,
                        ':tag_label'  => $tagLabel,
                        ':image_url'  => $imageUrl,
                        ':alt_text'   => $altText,
                        ':sort_order' => $sortOrder,
                        ':is_active'  => $isActive,
                        ':id'         => $id
                    ]);
                    Auth::setFlash('success', "Gallery image '{$title}' updated successfully.");
                } else {
                    $sql = "INSERT INTO `gallery_images`
                            (`title`, `category`, `tag_label`, `image_url`, `alt_text`, `sort_order`, `is_active`)
                            VALUES
                            (:title, :category, :tag_label, :image_url, :alt_text, :sort_order, :is_active)";
                    Database::execute($sql, [
                        ':title'      => $title,
                        ':category'   => $category,
                        ':tag_label'  => $tagLabel,
                        ':image_url'  => $imageUrl,
                        ':alt_text'   => $altText,
                        ':sort_order' => $sortOrder,
                        ':is_active'  => $isActive
                    ]);
                    Auth::setFlash('success', "New photo '{$title}' added to gallery.");
                }
                header('Location: gallery.php');
                exit;
            } catch (\Throwable $e) {
                error_log('[GALLERY SAVE ERROR]: ' . $e->getMessage());
                $error = 'Database write failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Image for Edit
$imageData = null;
if ($action === 'edit' && $imageId > 0) {
    try {
        $imageData = Database::fetchOne("SELECT * FROM `gallery_images` WHERE `id` = :id", [':id' => $imageId]);
        if (!$imageData) {
            Auth::setFlash('danger', 'Requested image record not found.');
            header('Location: gallery.php');
            exit;
        }
    } catch (\Throwable $e) {
        error_log('[GALLERY FETCH ERROR]: ' . $e->getMessage());
    }
}

$pageTitle = 'Photo Gallery';
require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-toolbar">
  <div>
    <h1 class="adm-page-title" style="margin-bottom: 0;">PHOTO GALLERY</h1>
    <p class="adm-page-subtitle">Upload and manage photos of the gym floor, equipment, workout areas, and facilities.</p>
  </div>
  <div>
    <?php if ($action === 'list'): ?>
      <a href="gallery.php?action=create" class="adm-btn adm-btn-lime adm-btn-sm">+ Add New Photo</a>
    <?php else: ?>
      <a href="gallery.php" class="adm-btn adm-btn-outline adm-btn-sm">&larr; Back to Gallery</a>
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
      <?= $action === 'edit' ? 'EDIT PHOTO: ' . e($imageData['title']) : 'UPLOAD NEW GYM PHOTO' ?>
    </h3>

    <form method="POST" action="gallery.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e($imageData['id'] ?? 0) ?>">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="title">Photo Title *</label>
          <input type="text" id="title" name="title" class="adm-input" required placeholder="e.g. Free Weights & Dumbbell Zone" value="<?= e($imageData['title'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="category">Category</label>
          <select id="category" name="category" class="adm-select">
            <?php foreach ($categories as $catKey => $catLabel): ?>
              <option value="<?= e($catKey) ?>" <?= ($imageData['category'] ?? '') === $catKey ? 'selected' : '' ?>>
                <?= e($catLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="tag_label">Badge / Tag (Optional)</label>
          <input type="text" id="tag_label" name="tag_label" class="adm-input" placeholder="e.g. Heavy Iron, Cardio Deck" value="<?= e($imageData['tag_label'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="alt_text">Alt Text (for SEO)</label>
          <input type="text" id="alt_text" name="alt_text" class="adm-input" placeholder="e.g. Modern gym equipment and racks" value="<?= e($imageData['alt_text'] ?? '') ?>">
        </div>
      </div>

      <!-- Secure Upload or URL -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: var(--adm-surface); padding: 1.25rem; border: 1px solid var(--adm-border);">
        <div>
          <label class="adm-label" for="image_file">Upload Photo File (JPEG, PNG, WebP, max 5MB)</label>
          <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="adm-input" style="padding: 0.35rem 0.5rem;">
        </div>
        <div>
          <label class="adm-label" for="image_url">OR Existing Image URL</label>
          <input type="text" id="image_url" name="image_url" class="adm-input" placeholder="https://images.unsplash.com/... or assets/images/..." value="<?= e($imageData['image_url'] ?? '') ?>">
          <?php if (!empty($imageData['image_url'])): ?>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
              <img src="<?= (str_starts_with($imageData['image_url'], 'http') ? '' : '../') . e($imageData['image_url']) ?>" alt="Preview" class="adm-thumb adm-thumb-lg">
              <span style="font-size: 0.75rem; color: var(--adm-silver-dim);">Current Photo</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
        <div>
          <label class="adm-label" for="sort_order">Display Order</label>
          <input type="number" id="sort_order" name="sort_order" class="adm-input" value="<?= e($imageData['sort_order'] ?? 10) ?>">
        </div>
        <div style="display: flex; align-items: flex-end; padding-bottom: 0.5rem;">
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input type="checkbox" name="is_active" value="1" <?= (!isset($imageData['is_active']) || !empty($imageData['is_active'])) ? 'checked' : '' ?> style="accent-color: var(--adm-lime); width: 18px; height: 18px;">
            <span style="font-size: 0.88rem; color: var(--adm-white);">Visible on Gym Website</span>
          </label>
        </div>
      </div>

      <div style="display: flex; gap: 1rem;">
        <button type="submit" class="adm-btn adm-btn-lime">Save Photo</button>
        <a href="gallery.php" class="adm-btn adm-btn-outline">Cancel</a>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- LIST VIEW -->
  <?php
  try {
      $images = Database::fetchAll("SELECT * FROM `gallery_images` ORDER BY `sort_order` ASC, `id` ASC");
  } catch (\Throwable $e) {
      error_log('[GALLERY LIST ERROR]: ' . $e->getMessage());
      $images = [];
  }
  ?>

  <?php if (empty($images)): ?>
    <div class="adm-card">
      <div class="adm-empty">
        <div class="adm-empty-title">No Photos Found</div>
        <div class="adm-empty-text">No gym photos uploaded yet. Add your first photo to display in the website gallery.</div>
        <a href="gallery.php?action=create" class="adm-btn adm-btn-lime">+ Upload First Photo</a>
      </div>
    </div>
  <?php else: ?>
    <div class="adm-table-container">
      <table class="adm-table adm-table-gallery">
        <thead>
          <tr>
            <th>Photo Preview</th>
            <th>Title</th>
            <th>Category</th>
            <th>Badge / Tag</th>
            <th>Status</th>
            <th>Order</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($images as $img): ?>
            <tr>
              <td>
                <a href="<?= (str_starts_with($img['image_url'], 'http') ? '' : '../') . e($img['image_url']) ?>" target="_blank">
                  <img 
                    src="<?= (str_starts_with($img['image_url'], 'http') ? '' : '../') . e($img['image_url']) ?>" 
                    alt="<?= e($img['title']) ?>" 
                    class="adm-thumb"
                    onerror="this.src='../assets/images/favicon.svg';"
                  >
                </a>
              </td>
              <td>
                <div style="font-weight: 700; font-size: 1rem; font-family: var(--adm-font-heading); color: var(--adm-white);">
                  <?= e($img['title']) ?>
                </div>
                <div style="font-size: 0.72rem; color: var(--adm-silver-dim); font-family: var(--adm-font-mono);">
                  <?= e(basename($img['image_url'])) ?>
                </div>
              </td>
              <td>
                <span class="adm-badge scheduled">
                  <?= e($categories[$img['category']] ?? $img['category']) ?>
                </span>
              </td>
              <td style="font-family: var(--adm-font-mono); font-size: 0.75rem; color: var(--adm-silver);">
                <?= e($img['tag_label']) ?>
              </td>
              <td>
                <form method="POST" action="gallery.php" style="display: inline;">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= e($img['id']) ?>">
                  <button type="submit" class="adm-badge <?= $img['is_active'] ? 'active' : 'inactive' ?>" style="cursor: pointer; border-style: solid;" title="Click to toggle visibility">
                    <?= $img['is_active'] ? '&bull; VISIBLE' : '&bull; HIDDEN' ?>
                  </button>
                </form>
              </td>
              <td style="font-family: var(--adm-font-mono); color: var(--adm-silver-dim);"><?= e($img['sort_order']) ?></td>
              <td style="text-align: right; white-space: nowrap;">
                <a href="gallery.php?action=edit&id=<?= e($img['id']) ?>" class="adm-btn adm-btn-outline adm-btn-sm" style="margin-right: 0.35rem;">Edit</a>
                <button type="button" class="adm-btn adm-btn-danger adm-btn-sm" onclick="openConfirmModal('gallery.php', <?= e($img['id']) ?>, 'Delete Photo', 'Are you sure you want to permanently delete this photo?')">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
