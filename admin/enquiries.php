<?php
/**
 * DARK MATTER — Elite Fitness
 * Guest Enquiries & Leads Telemetry Management
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

$statusList = ['pending', 'verified', 'scheduled', 'completed', 'cancelled'];
$typeList   = ['vip-pass', 'facility-tour', 'general-inquiry', 'trainer-consult', 'quick-pass', 'newsletter'];

// Handle Status Update or Deletion (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Action halted.');
        header('Location: enquiries.php');
        exit;
    }

    $postAction = $_POST['action'] ?? '';

    // 1. Update Status
    if ($postAction === 'update_status') {
        $id = (int) ($_POST['id'] ?? 0);
        $newStatus = Security::validateEnum($_POST['status'] ?? '', $statusList);
        if ($id > 0 && $newStatus) {
            try {
                Database::execute("UPDATE `enquiries` SET `status` = :st WHERE `id` = :id", [
                    ':st' => $newStatus,
                    ':id' => $id
                ]);
                Auth::setFlash('success', "Enquiry #{$id} status updated to " . strtoupper($newStatus) . ".");
            } catch (\Throwable $e) {
                error_log('[ENQUIRY STATUS UPDATE ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to update enquiry status.');
            }
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'enquiries.php'));
        exit;
    }

    // 2. Delete Enquiry
    if ($postAction === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                Database::execute("DELETE FROM `enquiries` WHERE `id` = :id", [':id' => $id]);
                Auth::setFlash('success', "Enquiry record #{$id} permanently deleted.");
            } catch (\Throwable $e) {
                error_log('[ENQUIRY DELETE ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to remove enquiry record.');
            }
        }
        header('Location: enquiries.php');
        exit;
    }
}

// Search and Filtering Parameters
$search     = trim((string) ($_GET['search'] ?? ''));
$filterStatus = Security::validateEnum($_GET['status'] ?? '', $statusList);
$filterType   = Security::validateEnum($_GET['type'] ?? '', $typeList);

$page  = max(1, (int) ($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

// Build Query
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(`full_name` LIKE :s1 OR `email` LIKE :s2 OR `phone` LIKE :s3 OR `message` LIKE :s4)";
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

if ($filterType) {
    $where[] = "`inquiry_type` = :type";
    $params[':type'] = $filterType;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count Total for Pagination
$totalRows = 0;
$enquiries = [];

try {
    $countSql = "SELECT COUNT(*) AS cnt FROM `enquiries` {$whereClause}";
    $res = Database::fetchOne($countSql, $params);
    $totalRows = (int) ($res['cnt'] ?? 0);

    $totalPages = max(1, (int) ceil($totalRows / $limit));
    $page = min($page, $totalPages);

    $sql = "SELECT * FROM `enquiries` 
            {$whereClause} 
            ORDER BY `id` DESC 
            LIMIT {$limit} OFFSET {$offset}";

    $enquiries = Database::fetchAll($sql, $params);
} catch (\Throwable $e) {
    error_log('[ENQUIRIES LIST QUERY ERROR]: ' . $e->getMessage());
    $totalPages = 1;
}

// Prepare query string for pagination links
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$pageUrl = 'enquiries.php?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=';
$pageTitle = 'Guest Enquiries';
require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-toolbar">
  <div>
    <h1 class="adm-page-title" style="margin-bottom: 0;">CUSTOMER ENQUIRIES</h1>
    <p class="adm-page-subtitle">View and manage contact inquiries, trial pass requests, and gym tour bookings.</p>
  </div>
  <div style="font-family: var(--adm-font-mono); font-size: 0.85rem; color: var(--adm-silver);">
    Total Inquiries: <strong style="color: var(--adm-lime);"><?= number_format($totalRows) ?></strong>
  </div>
</div>

<!-- Search & Filter Bar -->
<form method="GET" action="enquiries.php" class="adm-filter-bar">
  <div class="adm-input-group" style="flex: 2;">
    <input 
      type="text" 
      name="search" 
      class="adm-input" 
      placeholder="Search by name, email, phone, or keyword..." 
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
    <select name="type" class="adm-select" onchange="this.form.submit()">
      <option value="">All Inquiry Types</option>
      <?php foreach ($typeList as $tp): ?>
        <option value="<?= e($tp) ?>" <?= $filterType === $tp ? 'selected' : '' ?>>
          Type: <?= strtoupper(e(str_replace('-', ' ', $tp))) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit" class="adm-btn adm-btn-lime adm-btn-sm">Filter</button>
  <?php if ($search !== '' || $filterStatus || $filterType): ?>
    <a href="enquiries.php" class="adm-btn adm-btn-outline adm-btn-sm">Reset</a>
  <?php endif; ?>
</form>

<?php if (empty($enquiries)): ?>
  <div class="adm-card">
    <div class="adm-empty">
      <div class="adm-empty-title">No Enquiries Found</div>
      <div class="adm-empty-text">No customer inquiries match the active search or filter criteria.</div>
      <?php if ($search !== '' || $filterStatus || $filterType): ?>
        <a href="enquiries.php" class="adm-btn adm-btn-outline adm-btn-sm">Clear Active Filters</a>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="adm-table-container">
    <table class="adm-table adm-table-enquiries">
      <thead>
        <tr>
          <th>Ref ID</th>
          <th>Customer Name & Contact</th>
          <th>Inquiry Type</th>
          <th>Preferred Date & Slot</th>
          <th>Status</th>
          <th>Received Date</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($enquiries as $enq): ?>
          <?php $refCode = sprintf('DM-%d-%04d', (int)date('Y', strtotime($enq['created_at'])), (int)$enq['id']); ?>
          <tr>
            <td>
              <span class="adm-mono" style="font-weight: 700; color: var(--adm-lime); font-size: 0.78rem;">
                <?= e($refCode) ?>
              </span>
            </td>
            <td>
              <div style="font-weight: 600; color: var(--adm-white);"><?= e($enq['full_name']) ?></div>
              <div style="font-size: 0.75rem; color: var(--adm-silver-dim); font-family: var(--adm-font-mono);">
                <?= e($enq['email']) ?>
              </div>
              <div style="font-size: 0.72rem; color: var(--adm-silver-dim);">
                <?= e($enq['phone']) ?>
              </div>
            </td>
            <td>
              <span class="adm-mono" style="font-size: 0.78rem; text-transform: uppercase; color: var(--adm-silver);">
                <?= e(str_replace('-', ' ', $enq['inquiry_type'])) ?>
              </span>
              <?php if (!empty($enq['assigned_trainer'])): ?>
                <div style="font-size: 0.7rem; color: var(--adm-lime);">
                  Trainer: <?= e($enq['assigned_trainer']) ?>
                </div>
              <?php endif; ?>
            </td>
            <td style="font-size: 0.8rem; color: var(--adm-silver);">
              <?php if (!empty($enq['preferred_date'])): ?>
                <div><?= date('M d, Y', strtotime($enq['preferred_date'])) ?></div>
              <?php endif; ?>
              <?php if (!empty($enq['preferred_time_slot'])): ?>
                <div style="font-size: 0.72rem; color: var(--adm-silver-dim); font-family: var(--adm-font-mono); text-transform: uppercase;">
                  <?= e($enq['preferred_time_slot']) ?>
                </div>
              <?php endif; ?>
              <?php if (empty($enq['preferred_date']) && empty($enq['preferred_time_slot'])): ?>
                <span style="color: var(--adm-silver-dim); font-size: 0.72rem;">Immediate</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" action="enquiries.php" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?= e($enq['id']) ?>">
                <select name="status" class="adm-select" style="padding: 0.25rem 1.75rem 0.25rem 0.5rem; font-size: 0.75rem; font-family: var(--adm-font-mono); height: auto; width: auto;" onchange="this.form.submit()">
                  <?php foreach ($statusList as $st): ?>
                    <option value="<?= e($st) ?>" <?= $enq['status'] === $st ? 'selected' : '' ?>>
                      <?= strtoupper(e($st)) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td style="font-family: var(--adm-font-mono); font-size: 0.72rem; color: var(--adm-silver-dim); white-space: nowrap;">
              <?= date('Y-m-d H:i', strtotime($enq['created_at'])) ?>
            </td>
            <td style="text-align: right; white-space: nowrap;">
              <button 
                type="button" 
                class="adm-btn adm-btn-outline adm-btn-sm" 
                style="margin-right: 0.35rem;"
                onclick="viewEnquiryDetails(<?= htmlspecialchars(json_encode($enq), ENT_QUOTES, 'UTF-8') ?>)"
              >
                View Details
              </button>
              <button 
                type="button" 
                class="adm-btn adm-btn-danger adm-btn-sm" 
                onclick="openConfirmModal('enquiries.php', <?= e($enq['id']) ?>, 'Delete Enquiry <?= e($refCode) ?>', 'Are you sure you want to permanently delete this inquiry?')"
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

<!-- Enquiry Details Modal -->
<div class="adm-modal-backdrop" id="admEnquiryModal">
  <div class="adm-modal">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title" id="enqModalRef">Enquiry Details</h3>
      <button type="button" class="adm-modal-close" onclick="closeEnquiryModal()">&times;</button>
    </div>
    <div class="adm-modal-body" id="enqModalBody">
      <!-- Dynamic Payload Injected via JS -->
    </div>
    <div class="adm-modal-footer">
      <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" onclick="closeEnquiryModal()">Close</button>
    </div>
  </div>
</div>

<script>
function viewEnquiryDetails(data) {
  const modal = document.getElementById('admEnquiryModal');
  const title = document.getElementById('enqModalRef');
  const body = document.getElementById('enqModalBody');

  if (modal && body) {
    const padId = String(data.id).padStart(4, '0');
    title.textContent = `ENQUIRY DETAILS: #DM-${padId}`;

    body.innerHTML = `
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: var(--adm-surface-2); padding: 1rem; border: 1px solid var(--adm-border);">
        <div>
          <div class="adm-label">Full Name</div>
          <div style="font-weight: 700; font-size: 1.1rem; color: var(--adm-white);">${escapeHtml(data.full_name)}</div>
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

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <div class="adm-label">Inquiry Type</div>
          <div style="font-family: var(--adm-font-mono); font-size: 0.85rem; color: var(--adm-white); text-transform: uppercase;">
            ${escapeHtml(data.inquiry_type)}
          </div>
        </div>
        <div>
          <div class="adm-label">Preferred Date / Slot</div>
          <div style="font-size: 0.85rem; color: var(--adm-white);">
            ${escapeHtml(data.preferred_date || 'No Date Specified')} — ${escapeHtml(data.preferred_time_slot || 'Anytime')}
          </div>
        </div>
        <div>
          <div class="adm-label">Assigned Trainer</div>
          <div style="font-size: 0.85rem; color: var(--adm-lime); font-weight: 600;">
            ${escapeHtml(data.assigned_trainer || 'Unassigned / General')}
          </div>
        </div>
      </div>

      ${data.training_focus ? `
        <div style="margin-bottom: 1.25rem;">
          <div class="adm-label">Fitness Goal</div>
          <div style="font-size: 0.88rem; color: var(--adm-white); background: var(--adm-surface-2); padding: 0.75rem; border: 1px solid var(--adm-border);">
            ${escapeHtml(data.training_focus)}
          </div>
        </div>
      ` : ''}

      <div style="margin-bottom: 1.25rem;">
        <div class="adm-label">Customer Message / Notes</div>
        <div style="font-size: 0.88rem; color: var(--adm-silver); background: var(--adm-surface-2); padding: 0.85rem; border: 1px solid var(--adm-border); white-space: pre-wrap; line-height: 1.6;">
          ${escapeHtml(data.message || 'No additional message provided.')}
        </div>
      </div>

      <div style="font-family: var(--adm-font-mono); font-size: 0.72rem; color: var(--adm-silver-dim); border-top: 1px solid var(--adm-border); padding-top: 0.75rem;">
        <div>IP Address: <strong>${escapeHtml(data.ip_address)}</strong></div>
        <div>Submitted At: ${escapeHtml(data.created_at)}</div>
      </div>
    `;

    modal.classList.add('open');
  }
}

function closeEnquiryModal() {
  const modal = document.getElementById('admEnquiryModal');
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
