<?php
/**
 * DARK MATTER — Elite Fitness
 * Membership Applications Management
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

$statusList = ['pending', 'verified', 'active', 'rejected'];

// Handle Status Update or Deletion (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Action halted.');
        header('Location: applications.php');
        exit;
    }

    $postAction = $_POST['action'] ?? '';

    // 1. Update Status
    if ($postAction === 'update_status') {
        $id = (int) ($_POST['id'] ?? 0);
        $newStatus = Security::validateEnum($_POST['status'] ?? '', $statusList);
        if ($id > 0 && $newStatus) {
            try {
                Database::execute("UPDATE `membership_applications` SET `status` = :st WHERE `id` = :id", [
                    ':st' => $newStatus,
                    ':id' => $id
                ]);
                Auth::setFlash('success', "Application #{$id} status updated to " . strtoupper($newStatus) . ".");
            } catch (\Throwable $e) {
                error_log('[APPLICATION STATUS UPDATE ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to update application status.');
            }
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'applications.php'));
        exit;
    }

    // 2. Delete Application
    if ($postAction === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                Database::execute("DELETE FROM `membership_applications` WHERE `id` = :id", [':id' => $id]);
                Auth::setFlash('success', "Membership application #{$id} permanently removed.");
            } catch (\Throwable $e) {
                error_log('[APPLICATION DELETE ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to remove application record.');
            }
        }
        header('Location: applications.php');
        exit;
    }

    // 3. Convert Application to Member Roster
    if ($postAction === 'convert_to_member') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $app = Database::fetchOne("SELECT * FROM `membership_applications` WHERE `id` = :id", [':id' => $id]);
                if ($app) {
                    $nextNum = (int) Database::fetchOne("SELECT IFNULL(MAX(id), 0) + 1 AS nxt FROM `members`")['nxt'];
                    $memCode = sprintf('DM-%s-%04d', date('Y'), $nextNum);
                    $startDate = !empty($app['preferred_start_date']) ? $app['preferred_start_date'] : date('Y-m-d');
                    $expiryDate = ($app['billing_cycle'] === 'annual')
                        ? date('Y-m-d', strtotime($startDate . ' +1 year'))
                        : date('Y-m-d', strtotime($startDate . ' +1 month'));

                    Database::execute("
                        INSERT INTO `members` (
                            `membership_id`, `application_id`, `full_name`, `email`, `phone`,
                            `plan_id`, `plan_name`, `billing_cycle`, `start_date`, `expiry_date`,
                            `status`, `emergency_contact`, `notes`
                        ) VALUES (
                            :mid, :aid, :fn, :em, :ph,
                            :pid, :pn, :bc, :sd, :ed,
                            'active', :ec, :nt
                        )
                    ", [
                        ':mid' => $memCode,
                        ':aid' => $id,
                        ':fn'  => $app['full_name'],
                        ':em'  => $app['email'],
                        ':ph'  => $app['phone'],
                        ':pid' => (int) $app['plan_id'],
                        ':pn'  => $app['plan_name'],
                        ':bc'  => $app['billing_cycle'],
                        ':sd'  => $startDate,
                        ':ed'  => $expiryDate,
                        ':ec'  => 'Primary: ' . $app['phone'],
                        ':nt'  => "Enrolled from Application #{$id} [Facility: {$app['location']}]."
                    ]);

                    Database::execute("UPDATE `membership_applications` SET `status` = 'active' WHERE `id` = :id", [':id' => $id]);

                    Auth::setFlash('success', "Application #{$id} successfully promoted to Member Roster ({$memCode})!");
                }
            } catch (\Throwable $e) {
                error_log('[CONVERT TO MEMBER ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to convert application to member.');
            }
        }
        header('Location: members.php?tab=members');
        exit;
    }
}

// Fetch Plans for Plan Filter Dropdown
$allPlans = [];
try {
    $allPlans = Database::fetchAll("SELECT `id`, `name`, `slug` FROM `membership_plans` ORDER BY `sort_order` ASC");
} catch (\Throwable $e) {
    error_log('[FETCH PLANS ERROR]: ' . $e->getMessage());
}

// Search and Filtering Parameters
$search       = trim((string) ($_GET['search'] ?? ''));
$filterStatus = Security::validateEnum($_GET['status'] ?? '', $statusList);
$filterPlan   = isset($_GET['plan_id']) && $_GET['plan_id'] !== '' ? (int) $_GET['plan_id'] : null;

$page   = max(1, (int) ($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

// Build Query
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(`full_name` LIKE :s1 OR `email` LIKE :s2 OR `phone` LIKE :s3 OR `plan_name` LIKE :s4)";
    $searchWild = '%' . $search . '%';
    $params[':s1'] = $searchWild;
    $params[':s2'] = $searchWild;
    $params[':s3'] = $searchWild;
    $params[':s4'] = $searchWild;
}

if ($filterStatus) {
    $where[] = "`status` = :status";
    $params[':status'] = $filterStatus;
}

if ($filterPlan !== null && $filterPlan > 0) {
    $where[] = "`plan_id` = :plan_id";
    $params[':plan_id'] = $filterPlan;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count Total for Pagination
$totalRows = 0;
$applications = [];

try {
    $countSql = "SELECT COUNT(*) AS cnt FROM `membership_applications` {$whereClause}";
    $res = Database::fetchOne($countSql, $params);
    $totalRows = (int) ($res['cnt'] ?? 0);

    $totalPages = max(1, (int) ceil($totalRows / $limit));
    $page = min($page, $totalPages);

    $sql = "SELECT * FROM `membership_applications` 
            {$whereClause} 
            ORDER BY `id` DESC 
            LIMIT {$limit} OFFSET {$offset}";

    $applications = Database::fetchAll($sql, $params);
} catch (\Throwable $e) {
    error_log('[APPLICATIONS LIST QUERY ERROR]: ' . $e->getMessage());
    $totalPages = 1;
}

// Prepare query string for pagination links
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$pageUrl = 'applications.php?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';
$pageTitle = 'Online Membership Applications';
require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-toolbar">
  <div>
    <h1 class="adm-page-title" style="margin-bottom: 0;">ONLINE APPLICATIONS</h1>
    <p class="adm-page-subtitle">View incoming online gym membership applications and convert approved applicants to active members.</p>
  </div>
  <div style="font-family: var(--adm-font-mono); font-size: 0.85rem; color: var(--adm-silver);">
    Total Applications: <strong style="color: var(--adm-lime);"><?= number_format($totalRows) ?></strong>
  </div>
</div>

<!-- Search & Filter Bar -->
<form method="GET" action="applications.php" class="adm-filter-bar">
  <div class="adm-input-group" style="flex: 2;">
    <input 
      type="text" 
      name="search" 
      class="adm-input" 
      placeholder="Search by name, email, phone, or plan..." 
      value="<?= e($search) ?>"
    >
  </div>

  <div style="min-width: 160px;">
    <select name="status" class="adm-select" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <?php foreach ($statusList as $st): ?>
        <option value="<?= e($st) ?>" <?= $filterStatus === $st ? 'selected' : '' ?>>
          Status: <?= strtoupper(e($st)) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div style="min-width: 170px;">
    <select name="plan_id" class="adm-select" onchange="this.form.submit()">
      <option value="">All Plans</option>
      <?php foreach ($allPlans as $pl): ?>
        <option value="<?= e($pl['id']) ?>" <?= $filterPlan === (int)$pl['id'] ? 'selected' : '' ?>>
          Plan: <?= e($pl['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit" class="adm-btn adm-btn-lime adm-btn-sm">Filter</button>
  <?php if ($search !== '' || $filterStatus || $filterPlan !== null): ?>
    <a href="applications.php" class="adm-btn adm-btn-outline adm-btn-sm">Reset</a>
  <?php endif; ?>
</form>

<?php if (empty($applications)): ?>
  <div class="adm-card">
    <div class="adm-empty">
      <div class="adm-empty-title">No Applications Found</div>
      <div class="adm-empty-text">No online membership applications match the active filters.</div>
      <?php if ($search !== '' || $filterStatus || $filterPlan !== null): ?>
        <a href="applications.php" class="adm-btn adm-btn-outline adm-btn-sm">Clear Active Filters</a>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="adm-table-container">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Application ID</th>
          <th>Applicant Name & Contact</th>
          <th>Plan</th>
          <th>Billing Cycle</th>
          <th>Monthly Rate</th>
          <th>Status</th>
          <th>Applied Date</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($applications as $app): ?>
          <?php $appCode = sprintf('DM-MBR-%d-%04d', (int)date('Y', strtotime($app['created_at'])), (int)$app['id']); ?>
          <tr>
            <td>
              <span class="adm-mono" style="font-weight: 700; color: var(--adm-lime); font-size: 0.78rem;">
                <?= e($appCode) ?>
              </span>
            </td>
            <td>
              <div style="font-weight: 600; color: var(--adm-white);"><?= e($app['full_name']) ?></div>
              <div style="font-size: 0.75rem; color: var(--adm-silver-dim); font-family: var(--adm-font-mono);">
                <?= e($app['email']) ?>
              </div>
              <div style="font-size: 0.72rem; color: var(--adm-silver-dim);">
                <?= e($app['phone']) ?>
              </div>
            </td>
            <td>
              <span style="font-family: var(--adm-font-heading); font-size: 1rem; color: var(--adm-white); font-weight: 700;">
                <?= e($app['plan_name']) ?>
              </span>
            </td>
            <td>
              <span class="adm-mono" style="font-size: 0.75rem; text-transform: uppercase; color: var(--adm-silver);">
                <?= e($app['billing_cycle']) ?>
              </span>
            </td>
            <td style="font-family: var(--adm-font-mono); font-weight: 700; color: var(--adm-lime);">
              $<?= number_format((float)$app['amount_monthly'], 2) ?>/mo
            </td>
            <td>
              <form method="POST" action="applications.php" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?= e($app['id']) ?>">
                <select name="status" class="adm-select" style="padding: 0.25rem 1.75rem 0.25rem 0.5rem; font-size: 0.75rem; font-family: var(--adm-font-mono); height: auto; width: auto;" onchange="this.form.submit()">
                  <?php foreach ($statusList as $st): ?>
                    <option value="<?= e($st) ?>" <?= $app['status'] === $st ? 'selected' : '' ?>>
                      <?= strtoupper(e($st)) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td style="font-family: var(--adm-font-mono); font-size: 0.72rem; color: var(--adm-silver-dim); white-space: nowrap;">
              <?= date('Y-m-d H:i', strtotime($app['created_at'])) ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <?php if ($app['status'] !== 'active'): ?>
                <form method="POST" action="applications.php" style="display:inline; margin-right: 0.35rem;" onsubmit="return confirm('Add applicant <?= e($app['full_name']) ?> to Active Gym Members?');">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="action" value="convert_to_member">
                  <input type="hidden" name="id" value="<?= (int) $app['id'] ?>">
                  <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm" title="Convert to Active Member">
                    + Make Member
                  </button>
                </form>
              <?php endif; ?>
              <button 
                type="button" 
                class="adm-btn adm-btn-outline adm-btn-sm" 
                style="margin-right: 0.35rem;"
                onclick="viewAppDetails(<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>)"
              >
                View Details
              </button>
              <button 
                type="button" 
                class="adm-btn adm-btn-danger adm-btn-sm" 
                onclick="openConfirmModal('applications.php', <?= e($app['id']) ?>, 'Delete Application <?= e($appCode) ?>', 'Are you sure you want to delete this application?')"
              >
                Delete
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination Controls -->
  <?php if ($totalPages > 1): ?>
    <div class="adm-pagination">
      <div>
        Showing page <?= $page ?> of <?= $totalPages ?> (<?= number_format($totalRows) ?> total records)
      </div>
      <div class="adm-page-links">
        <a href="<?= $pageUrl . max(1, $page - 1) ?>" class="adm-page-link <?= $page <= 1 ? 'disabled' : '' ?>">&larr;</a>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
            <a href="<?= $pageUrl . $i ?>" class="adm-page-link <?= $i === $page ? 'active' : '' ?>">
              <?= $i ?>
            </a>
          <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
            <span style="color: var(--adm-silver-dim); padding: 0 0.25rem;">...</span>
          <?php endif; ?>
        <?php endfor; ?>
        <a href="<?= $pageUrl . min($totalPages, $page + 1) ?>" class="adm-page-link <?= $page >= $totalPages ? 'disabled' : '' ?>">&rarr;</a>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>

<!-- Application Detail Modal -->
<div class="adm-modal-backdrop" id="admAppModal">
  <div class="adm-modal">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title" id="appModalRef">Application Details</h3>
      <button type="button" class="adm-modal-close" onclick="closeAppModal()">&times;</button>
    </div>
    <div class="adm-modal-body" id="appModalBody">
      <!-- Injected via JS -->
    </div>
    <div class="adm-modal-footer">
      <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" onclick="closeAppModal()">Close</button>
    </div>
  </div>
</div>

<script>
function viewAppDetails(data) {
  const modal = document.getElementById('admAppModal');
  const title = document.getElementById('appModalRef');
  const body = document.getElementById('appModalBody');

  if (modal && body) {
    const padId = String(data.id).padStart(4, '0');
    title.textContent = `APPLICATION: #DM-APP-${padId}`;

    body.innerHTML = `
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: var(--adm-surface-2); padding: 1rem; border: 1px solid var(--adm-border);">
        <div>
          <div class="adm-label">Full Name</div>
          <div style="font-weight: 700; font-size: 1.15rem; color: var(--adm-white);">${escapeHtml(data.full_name)}</div>
        </div>
        <div>
          <div class="adm-label">Email Address</div>
          <div style="font-family: var(--adm-font-mono); font-size: 0.85rem; color: var(--adm-lime);">${escapeHtml(data.email)}</div>
        </div>
        <div>
          <div class="adm-label">Phone Number</div>
          <div style="font-family: var(--adm-font-mono); font-size: 0.85rem; color: var(--adm-white);">${escapeHtml(data.phone)}</div>
        </div>
        <div>
          <div class="adm-label">Gym Branch</div>
          <div style="font-size: 0.85rem; color: var(--adm-silver);">${escapeHtml(data.location || 'Main Gym')}</div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: rgba(0, 229, 255, 0.06); padding: 1rem; border: 1px solid var(--adm-lime);">
        <div>
          <div class="adm-label" style="color: var(--adm-lime);">Selected Plan</div>
          <div style="font-family: var(--adm-font-heading); font-size: 1.35rem; color: var(--adm-white); font-weight: 800;">
            ${escapeHtml(data.plan_name)}
          </div>
        </div>
        <div>
          <div class="adm-label" style="color: var(--adm-lime);">Billing Cycle</div>
          <div style="font-family: var(--adm-font-mono); font-size: 0.95rem; color: var(--adm-white); text-transform: uppercase;">
            ${escapeHtml(data.billing_cycle)}
          </div>
        </div>
        <div>
          <div class="adm-label" style="color: var(--adm-lime);">Monthly Price</div>
          <div style="font-family: var(--adm-font-mono); font-size: 1.2rem; color: var(--adm-lime); font-weight: 700;">
            $${parseFloat(data.amount_monthly).toFixed(2)} / month
          </div>
        </div>
      </div>

      <div style="font-family: var(--adm-font-mono); font-size: 0.72rem; color: var(--adm-silver-dim); border-top: 1px solid var(--adm-border); padding-top: 0.75rem;">
        <div>IP Address: <strong>${escapeHtml(data.ip_address)}</strong></div>
        <div>Applied Date: ${escapeHtml(data.created_at)}</div>
      </div>
    `;

    modal.classList.add('open');
  }
}

function closeAppModal() {
  const modal = document.getElementById('admAppModal');
  if (modal) {
    modal.classList.remove('open');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
