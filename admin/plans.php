<?php
/**
 * DARK MATTER — Elite Fitness
 * Membership Plans Management
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

$action = $_GET['action'] ?? 'list';
$planId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

// Handle State-Changing POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Request halted.');
        header('Location: plans.php');
        exit;
    }

    $postAction = $_POST['action'] ?? '';

    // 1. Toggle Active Status
    if ($postAction === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $plan = Database::fetchOne("SELECT `id`, `is_active`, `name` FROM `membership_plans` WHERE `id` = :id", [':id' => $id]);
            if ($plan) {
                $newStatus = $plan['is_active'] ? 0 : 1;
                Database::execute("UPDATE `membership_plans` SET `is_active` = :st WHERE `id` = :id", [
                    ':st' => $newStatus,
                    ':id' => $id
                ]);
                $statusWord = $newStatus ? 'activated' : 'deactivated';
                Auth::setFlash('success', "Plan '{$plan['name']}' {$statusWord} successfully.");
            }
        } catch (\Throwable $e) {
            error_log('[PLAN TOGGLE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to update plan status.');
        }
        header('Location: plans.php');
        exit;
    }

    // 2. Delete Plan
    if ($postAction === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            // Check for foreign key references in membership_applications
            $appCount = Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM `membership_applications` WHERE `plan_id` = :id",
                [':id' => $id]
            );

            if (!empty($appCount['cnt']) && (int) $appCount['cnt'] > 0) {
                Auth::setFlash('warning', "Cannot delete plan: {$appCount['cnt']} existing applications reference this plan. Deactivate it instead.");
                header('Location: plans.php');
                exit;
            }

            Database::execute("DELETE FROM `membership_plans` WHERE `id` = :id", [':id' => $id]);
            Auth::setFlash('success', 'Membership plan permanently removed.');
        } catch (\Throwable $e) {
            error_log('[PLAN DELETE ERROR]: ' . $e->getMessage());
            Auth::setFlash('danger', 'Failed to delete membership plan.');
        }
        header('Location: plans.php');
        exit;
    }

    // 3. Save (Create or Update)
    if ($postAction === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = Security::sanitizeString($_POST['name'] ?? '', 100);
        $slug = Security::sanitizeString($_POST['slug'] ?? '', 50);
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        }
        $tierLabel = Security::sanitizeString($_POST['tier_label'] ?? '', 100);
        $tagline = Security::sanitizeString($_POST['tagline'] ?? '', 255);
        $monthlyPrice = (float) ($_POST['monthly_price'] ?? 0);
        $annualMonthlyPrice = (float) ($_POST['annual_monthly_price'] ?? 0);
        $noteMonthly = Security::sanitizeString($_POST['note_monthly'] ?? 'Billed monthly • Cancel anytime', 255);
        $noteAnnual = Security::sanitizeString($_POST['note_annual'] ?? 'Billed annually • Save 20%', 255);
        $isPopular = !empty($_POST['is_popular']) ? 1 : 0;
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        // Process features (newline-separated)
        $rawFeatures = trim((string) ($_POST['features'] ?? ''));
        $featuresList = [];
        if ($rawFeatures !== '') {
            $lines = explode("\n", $rawFeatures);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    $featuresList[] = $trimmed;
                }
            }
        }
        $featuresJson = json_encode($featuresList, JSON_UNESCAPED_UNICODE);

        if ($name === '' || $monthlyPrice <= 0) {
            $error = 'Plan name and a valid monthly price are required.';
        } else {
            try {
                // Check slug uniqueness
                $existingSlug = Database::fetchOne(
                    "SELECT `id` FROM `membership_plans` WHERE `slug` = :slug AND `id` != :id",
                    [':slug' => $slug, ':id' => $id]
                );

                if ($existingSlug) {
                    $error = "The slug identifier '{$slug}' is already in use by another plan.";
                } else {
                    if ($id > 0) {
                        // Update
                        $sql = "UPDATE `membership_plans` SET
                                `slug` = :slug,
                                `name` = :name,
                                `tier_label` = :tier_label,
                                `tagline` = :tagline,
                                `monthly_price` = :monthly_price,
                                `annual_monthly_price` = :annual_monthly_price,
                                `note_monthly` = :note_monthly,
                                `note_annual` = :note_annual,
                                `is_popular` = :is_popular,
                                `features` = :features,
                                `sort_order` = :sort_order,
                                `is_active` = :is_active
                                WHERE `id` = :id";
                        Database::execute($sql, [
                            ':slug'                 => $slug,
                            ':name'                 => $name,
                            ':tier_label'           => $tierLabel,
                            ':tagline'              => $tagline,
                            ':monthly_price'        => $monthlyPrice,
                            ':annual_monthly_price' => $annualMonthlyPrice,
                            ':note_monthly'         => $noteMonthly,
                            ':note_annual'          => $noteAnnual,
                            ':is_popular'           => $isPopular,
                            ':features'             => $featuresJson,
                            ':sort_order'           => $sortOrder,
                            ':is_active'            => $isActive,
                            ':id'                   => $id
                        ]);
                        Auth::setFlash('success', "Plan '{$name}' updated successfully.");
                    } else {
                        // Insert
                        $sql = "INSERT INTO `membership_plans`
                                (`slug`, `name`, `tier_label`, `tagline`, `monthly_price`, `annual_monthly_price`, `note_monthly`, `note_annual`, `is_popular`, `features`, `sort_order`, `is_active`)
                                VALUES
                                (:slug, :name, :tier_label, :tagline, :monthly_price, :annual_monthly_price, :note_monthly, :note_annual, :is_popular, :features, :sort_order, :is_active)";
                        Database::execute($sql, [
                            ':slug'                 => $slug,
                            ':name'                 => $name,
                            ':tier_label'           => $tierLabel,
                            ':tagline'              => $tagline,
                            ':monthly_price'        => $monthlyPrice,
                            ':annual_monthly_price' => $annualMonthlyPrice,
                            ':note_monthly'         => $noteMonthly,
                            ':note_annual'          => $noteAnnual,
                            ':is_popular'           => $isPopular,
                            ':features'             => $featuresJson,
                            ':sort_order'           => $sortOrder,
                            ':is_active'            => $isActive
                        ]);
                        Auth::setFlash('success', "New membership plan '{$name}' created.");
                    }
                    header('Location: plans.php');
                    exit;
                }
            } catch (\Throwable $e) {
                error_log('[PLAN SAVE ERROR]: ' . $e->getMessage());
                $error = 'Database write failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Plan for Edit Form
$planData = null;
if ($action === 'edit' && $planId > 0) {
    try {
        $planData = Database::fetchOne("SELECT * FROM `membership_plans` WHERE `id` = :id", [':id' => $planId]);
        if (!$planData) {
            Auth::setFlash('danger', 'Requested membership plan not found.');
            header('Location: plans.php');
            exit;
        }
    } catch (\Throwable $e) {
        error_log('[PLAN FETCH ERROR]: ' . $e->getMessage());
    }
}

$pageTitle = 'Membership Plans';
require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-toolbar">
  <div>
    <h1 class="adm-page-title" style="margin-bottom: 0;">MEMBERSHIP PLANS</h1>
    <p class="adm-page-subtitle">Create, edit, or remove gym membership plans, pricing, and benefits.</p>
  </div>
  <div>
    <?php if ($action === 'list'): ?>
      <a href="plans.php?action=create" class="adm-btn adm-btn-lime adm-btn-sm">+ Add New Plan</a>
    <?php else: ?>
      <a href="plans.php" class="adm-btn adm-btn-outline adm-btn-sm">&larr; Back to Plans List</a>
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
      <?= $action === 'edit' ? 'EDIT PLAN: ' . e($planData['name']) : 'ADD NEW MEMBERSHIP PLAN' ?>
    </h3>

    <form method="POST" action="plans.php">
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e($planData['id'] ?? 0) ?>">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="name">Plan Name *</label>
          <input type="text" id="name" name="name" class="adm-input" required placeholder="e.g. Standard, Premium, VIP" value="<?= e($planData['name'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="slug">URL Slug (Optional)</label>
          <input type="text" id="slug" name="slug" class="adm-input" placeholder="e.g. standard-plan" value="<?= e($planData['slug'] ?? '') ?>">
          <small style="color: var(--adm-silver-dim); font-size: 0.72rem;">Leave blank to auto-generate from name.</small>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="tier_label">Badge / Subtitle (Optional)</label>
          <input type="text" id="tier_label" name="tier_label" class="adm-input" placeholder="e.g. Most Popular, Best Value" value="<?= e($planData['tier_label'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="sort_order">Display Order</label>
          <input type="number" id="sort_order" name="sort_order" class="adm-input" value="<?= e($planData['sort_order'] ?? 10) ?>">
        </div>
      </div>

      <div style="margin-bottom: 1.25rem;">
        <label class="adm-label" for="tagline">Short Description / Summary</label>
        <input type="text" id="tagline" name="tagline" class="adm-input" placeholder="Full gym access, certified trainers, locker and shower..." value="<?= e($planData['tagline'] ?? '') ?>">
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="monthly_price">Monthly Price (₹) *</label>
          <input type="number" step="0.01" min="0" id="monthly_price" name="monthly_price" class="adm-input" required placeholder="2500.00" value="<?= e($planData['monthly_price'] ?? '') ?>">
        </div>
        <div>
          <label class="adm-label" for="annual_monthly_price">Annual Price per Month (₹)</label>
          <input type="number" step="0.01" min="0" id="annual_monthly_price" name="annual_monthly_price" class="adm-input" placeholder="2000.00" value="<?= e($planData['annual_monthly_price'] ?? '') ?>">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="adm-label" for="note_monthly">Monthly Billing Note</label>
          <input type="text" id="note_monthly" name="note_monthly" class="adm-input" value="<?= e($planData['note_monthly'] ?? 'Billed monthly • Cancel anytime') ?>">
        </div>
        <div>
          <label class="adm-label" for="note_annual">Annual Billing Note</label>
          <input type="text" id="note_annual" name="note_annual" class="adm-input" value="<?= e($planData['note_annual'] ?? 'Billed annually • Save 20% on yearly membership') ?>">
        </div>
      </div>

      <div style="margin-bottom: 1.5rem;">
        <label class="adm-label" for="features">Plan Benefits / Features (1 item per line)</label>
        <?php
          $featureLines = '';
          if (!empty($planData['features'])) {
              $decoded = is_string($planData['features']) ? json_decode($planData['features'], true) : $planData['features'];
              if (is_array($decoded)) {
                  $lines = [];
                  foreach ($decoded as $item) {
                      $lines[] = is_array($item) ? ($item['name'] ?? '') : (string) $item;
                  }
                  $featureLines = implode("\n", array_filter($lines));
              }
          }
        ?>
        <textarea id="features" name="features" rows="7" class="adm-textarea" placeholder="24/7 Unlimited Gym Floor Access&#10;Free Locker & Shower Access&#10;1 Complimentary Personal Trainer Consultation&#10;Access to Steam & Sauna"><?= e($featureLines) ?></textarea>
      </div>

      <div style="display: flex; gap: 2rem; margin-bottom: 1.75rem;">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
          <input type="checkbox" name="is_popular" value="1" <?= !empty($planData['is_popular']) ? 'checked' : '' ?> style="accent-color: var(--adm-lime); width: 18px; height: 18px;">
          <span style="font-size: 0.88rem; color: var(--adm-white);">Highlight as "Most Popular"</span>
        </label>
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
          <input type="checkbox" name="is_active" value="1" <?= (!isset($planData['is_active']) || !empty($planData['is_active'])) ? 'checked' : '' ?> style="accent-color: var(--adm-lime); width: 18px; height: 18px;">
          <span style="font-size: 0.88rem; color: var(--adm-white);">Plan Active (Visible on gym website)</span>
        </label>
      </div>

      <div style="display: flex; gap: 1rem;">
        <button type="submit" class="adm-btn adm-btn-lime">Save Membership Plan</button>
        <a href="plans.php" class="adm-btn adm-btn-outline">Cancel</a>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- LIST VIEW -->
  <?php
  try {
      $plans = Database::fetchAll("SELECT * FROM `membership_plans` ORDER BY `sort_order` ASC, `id` ASC");
  } catch (\Throwable $e) {
      error_log('[PLANS LIST ERROR]: ' . $e->getMessage());
      $plans = [];
  }
  ?>

  <?php if (empty($plans)): ?>
    <div class="adm-card">
      <div class="adm-empty">
        <div class="adm-empty-title">No Membership Plans Found</div>
        <div class="adm-empty-text">No membership plans created yet. Add your first plan to display it on the website.</div>
        <a href="plans.php?action=create" class="adm-btn adm-btn-lime">+ Add First Plan</a>
      </div>
    </div>
  <?php else: ?>
    <div class="adm-table-container">
      <table class="adm-table adm-table-plans">
        <thead>
          <tr>
            <th>Order</th>
            <th>Plan Name</th>
            <th>Slug</th>
            <th>Monthly Price</th>
            <th>Annual Price</th>
            <th>Status</th>
            <th>Featured</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($plans as $p): ?>
            <tr>
              <td style="font-family: var(--adm-font-mono); color: var(--adm-silver-dim);"><?= e($p['sort_order']) ?></td>
              <td>
                <div style="font-weight: 700; font-size: 1.05rem; font-family: var(--adm-font-heading); color: var(--adm-white);">
                  <?= e($p['name']) ?>
                </div>
                <div style="font-size: 0.72rem; color: var(--adm-silver-dim);"><?= e($p['tier_label']) ?></div>
              </td>
              <td style="font-family: var(--adm-font-mono); font-size: 0.78rem; color: var(--adm-silver);">
                <?= e($p['slug']) ?>
              </td>
              <td style="font-family: var(--adm-font-mono); font-weight: 700; color: var(--adm-lime);">
                ₹<?= number_format((float)$p['monthly_price'], 2) ?>
              </td>
              <td style="font-family: var(--adm-font-mono); color: var(--adm-silver);">
                ₹<?= number_format((float)$p['annual_monthly_price'], 2) ?>
              </td>
              <td>
                <form method="POST" action="plans.php" style="display: inline;">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                  <button type="submit" class="adm-badge <?= $p['is_active'] ? 'active' : 'inactive' ?>" style="cursor: pointer; border-style: solid;" title="Click to toggle active status">
                    <?= $p['is_active'] ? '&bull; ACTIVE' : '&bull; INACTIVE' ?>
                  </button>
                </form>
              </td>
              <td>
                <?php if ($p['is_popular']): ?>
                  <span class="adm-badge" style="background: rgba(0, 229, 255, 0.15); color: var(--adm-lime); border: 1px solid var(--adm-lime);">POPULAR</span>
                <?php else: ?>
                  <span style="color: var(--adm-silver-dim); font-size: 0.75rem;">—</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <a href="plans.php?action=edit&id=<?= e($p['id']) ?>" class="adm-btn adm-btn-outline adm-btn-sm" style="margin-right: 0.35rem;">Edit</a>
                <button type="button" class="adm-btn adm-btn-danger adm-btn-sm" onclick="openConfirmModal('plans.php', <?= e($p['id']) ?>, 'Delete Plan <?= e($p['name']) ?>', 'Are you sure you want to permanently delete plan <?= e($p['name']) ?>? This cannot be undone.')">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
