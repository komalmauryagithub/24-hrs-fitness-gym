<?php
/**
 * DARK MATTER — Elite Fitness
 * Member Management Module (Pillar 4)
 * Handles Member Profiles, Membership IDs, Expiry Dates, Status, Payment Records & Floor Attendance.
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

$pageTitle = 'Gym Members & Attendance';

// Active Tab ('members', 'payments', 'attendance')
$currentTab = Security::validateEnum($_GET['tab'] ?? 'members', ['members', 'payments', 'attendance']) ?? 'members';

// Handle POST actions with CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        Auth::setFlash('danger', 'Security token invalid or expired. Action halted.');
        header('Location: members.php?tab=' . urlencode($currentTab));
        exit;
    }

    $postAction = $_POST['action'] ?? '';

    // 1. CREATE NEW MEMBER
    if ($postAction === 'create_member') {
        $fullName = Security::sanitizeString($_POST['full_name'] ?? '', 100);
        $email = Security::validateEmail($_POST['email'] ?? '');
        $phone = Security::validatePhone($_POST['phone'] ?? '');
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $billingCycle = Security::validateEnum($_POST['billing_cycle'] ?? 'monthly', ['monthly', 'annual']) ?? 'monthly';
        $startDate = Security::sanitizeString($_POST['start_date'] ?? date('Y-m-d'), 20);
        $expiryDate = Security::sanitizeString($_POST['expiry_date'] ?? date('Y-m-d', strtotime('+1 month')), 20);
        $status = Security::validateEnum($_POST['status'] ?? 'active', ['active', 'expired', 'pending']) ?? 'active';
        $emergency = Security::sanitizeString($_POST['emergency_contact'] ?? '', 100);
        $notes = Security::sanitizeString($_POST['notes'] ?? '', 500);

        // Auto-generate membership ID if empty
        $customId = Security::sanitizeString($_POST['membership_id'] ?? '', 30);
        if ($customId === '') {
            $nextNum = (int) Database::fetchOne("SELECT IFNULL(MAX(id), 0) + 1 AS nxt FROM `members`")['nxt'];
            $customId = sprintf('DM-%s-%04d', date('Y'), $nextNum);
        }

        if ($fullName === '' || !$email || !$phone || $planId <= 0) {
            Auth::setFlash('danger', 'Required fields missing: Valid Name, Email, Phone, and Plan are required.');
        } else {
            try {
                $plan = Database::fetchOne("SELECT `name` FROM `membership_plans` WHERE `id` = :id", [':id' => $planId]);
                $planName = $plan['name'] ?? 'STANDARD PROTOCOL';

                Database::execute("
                    INSERT INTO `members` (
                        `membership_id`, `full_name`, `email`, `phone`, `plan_id`, `plan_name`,
                        `billing_cycle`, `start_date`, `expiry_date`, `status`, `emergency_contact`, `notes`
                    ) VALUES (
                        :mid, :fn, :em, :ph, :pid, :pn,
                        :bc, :sd, :ed, :st, :ec, :nt
                    )
                ", [
                    ':mid' => $customId,
                    ':fn'  => $fullName,
                    ':em'  => $email,
                    ':ph'  => $phone,
                    ':pid' => $planId,
                    ':pn'  => $planName,
                    ':bc'  => $billingCycle,
                    ':sd'  => $startDate,
                    ':ed'  => $expiryDate,
                    ':st'  => $status,
                    ':ec'  => $emergency,
                    ':nt'  => $notes
                ]);

                // Also record initial payment if requested
                $initialPayment = (float) ($_POST['initial_payment_amount'] ?? 0);
                if ($initialPayment > 0) {
                    $newMemberId = (int) Database::lastInsertId();
                    $payMethod = Security::sanitizeString($_POST['initial_payment_method'] ?? 'Credit Card', 50);
                    $txId = 'TXN-DM-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

                    Database::execute("
                        INSERT INTO `member_payments` (
                            `member_id`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `payment_date`, `notes`
                        ) VALUES (
                            :mid, :tx, :amt, :pm, 'completed', :pd, :nt
                        )
                    ", [
                        ':mid' => $newMemberId,
                        ':tx'  => $txId,
                        ':amt' => $initialPayment,
                        ':pm'  => in_array($payMethod, ['UPI', 'Credit Card', 'Debit Card', 'Cash / Reception', 'Bank Transfer']) ? $payMethod : 'Cash / Reception',
                        ':pd'  => $startDate,
                        ':nt'  => 'Initial enrollment payment logged at creation'
                    ]);
                }

                Auth::setFlash('success', "Member profile {$customId} ({$fullName}) registered successfully.");
            } catch (\Throwable $e) {
                error_log('[CREATE MEMBER ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to save member profile: ' . (str_contains($e->getMessage(), 'Duplicate') ? 'Membership ID or Email already exists.' : 'Database error.'));
            }
        }
        header('Location: members.php?tab=members');
        exit;
    }

    // 2. UPDATE MEMBER
    if ($postAction === 'edit_member') {
        $id = (int) ($_POST['member_id'] ?? 0);
        $fullName = Security::sanitizeString($_POST['full_name'] ?? '', 100);
        $email = Security::validateEmail($_POST['email'] ?? '');
        $phone = Security::validatePhone($_POST['phone'] ?? '');
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $billingCycle = Security::validateEnum($_POST['billing_cycle'] ?? 'monthly', ['monthly', 'annual']) ?? 'monthly';
        $startDate = Security::sanitizeString($_POST['start_date'] ?? date('Y-m-d'), 20);
        $expiryDate = Security::sanitizeString($_POST['expiry_date'] ?? date('Y-m-d'), 20);
        $status = Security::validateEnum($_POST['status'] ?? 'active', ['active', 'expired', 'pending']) ?? 'active';
        $emergency = Security::sanitizeString($_POST['emergency_contact'] ?? '', 100);
        $notes = Security::sanitizeString($_POST['notes'] ?? '', 500);

        if ($id > 0 && $fullName !== '' && $email && $phone && $planId > 0) {
            try {
                $plan = Database::fetchOne("SELECT `name` FROM `membership_plans` WHERE `id` = :id", [':id' => $planId]);
                $planName = $plan['name'] ?? 'STANDARD PROTOCOL';

                Database::execute("
                    UPDATE `members` SET
                        `full_name` = :fn, `email` = :em, `phone` = :ph,
                        `plan_id` = :pid, `plan_name` = :pn, `billing_cycle` = :bc,
                        `start_date` = :sd, `expiry_date` = :ed, `status` = :st,
                        `emergency_contact` = :ec, `notes` = :nt
                    WHERE `id` = :id
                ", [
                    ':fn'  => $fullName,
                    ':em'  => $email,
                    ':ph'  => $phone,
                    ':pid' => $planId,
                    ':pn'  => $planName,
                    ':bc'  => $billingCycle,
                    ':sd'  => $startDate,
                    ':ed'  => $expiryDate,
                    ':st'  => $status,
                    ':ec'  => $emergency,
                    ':nt'  => $notes,
                    ':id'  => $id
                ]);
                Auth::setFlash('success', "Member profile #{$id} updated successfully.");
            } catch (\Throwable $e) {
                error_log('[UPDATE MEMBER ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to update member profile.');
            }
        }
        header('Location: members.php?tab=members');
        exit;
    }

    // 3. QUICK STATUS CHANGE
    if ($postAction === 'update_member_status') {
        $id = (int) ($_POST['member_id'] ?? 0);
        $newStatus = Security::validateEnum($_POST['status'] ?? '', ['active', 'expired', 'pending']);
        if ($id > 0 && $newStatus) {
            try {
                Database::execute("UPDATE `members` SET `status` = :st WHERE `id` = :id", [
                    ':st' => $newStatus,
                    ':id' => $id
                ]);
                Auth::setFlash('success', "Member status switched to " . strtoupper($newStatus) . ".");
            } catch (\Throwable $e) {
                Auth::setFlash('danger', 'Failed to update status.');
            }
        }
        header('Location: members.php?tab=members');
        exit;
    }

    // 4. DELETE MEMBER
    if ($postAction === 'delete_member') {
        $id = (int) ($_POST['member_id'] ?? 0);
        if ($id > 0) {
            try {
                Database::execute("DELETE FROM `members` WHERE `id` = :id", [':id' => $id]);
                Auth::setFlash('success', "Member record permanently removed.");
            } catch (\Throwable $e) {
                Auth::setFlash('danger', 'Failed to remove member record.');
            }
        }
        header('Location: members.php?tab=members');
        exit;
    }

    // 5. RECORD PAYMENT
    if ($postAction === 'record_payment') {
        $memberId = (int) ($_POST['member_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $method = Security::sanitizeString($_POST['payment_method'] ?? 'UPI', 50);
        $payStatus = Security::validateEnum($_POST['payment_status'] ?? 'completed', ['completed', 'pending', 'refunded', 'failed']) ?? 'completed';
        $payDate = Security::sanitizeString($_POST['payment_date'] ?? date('Y-m-d'), 20);
        $notes = Security::sanitizeString($_POST['notes'] ?? '', 255);

        $customTx = Security::sanitizeString($_POST['transaction_id'] ?? '', 100);
        if ($customTx === '') {
            $customTx = 'TXN-DM-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        }

        if ($memberId > 0 && $amount > 0) {
            try {
                Database::execute("
                    INSERT INTO `member_payments` (
                        `member_id`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `payment_date`, `notes`
                    ) VALUES (
                        :mid, :tx, :amt, :pm, :ps, :pd, :nt
                    )
                ", [
                    ':mid' => $memberId,
                    ':tx'  => $customTx,
                    ':amt' => $amount,
                    ':pm'  => in_array($method, ['UPI', 'Credit Card', 'Debit Card', 'Cash / Reception', 'Bank Transfer']) ? $method : 'UPI',
                    ':ps'  => $payStatus,
                    ':pd'  => $payDate,
                    ':nt'  => $notes
                ]);
                Auth::setFlash('success', "Payment record of \${$amount} logged (Ref: {$customTx}).");
            } catch (\Throwable $e) {
                error_log('[RECORD PAYMENT ERROR]: ' . $e->getMessage());
                Auth::setFlash('danger', 'Failed to record payment.');
            }
        } else {
            Auth::setFlash('danger', 'Please provide a valid Member and Amount.');
        }
        header('Location: members.php?tab=payments');
        exit;
    }

    // 6. DELETE PAYMENT
    if ($postAction === 'delete_payment') {
        $payId = (int) ($_POST['payment_id'] ?? 0);
        if ($payId > 0) {
            try {
                Database::execute("DELETE FROM `member_payments` WHERE `id` = :id", [':id' => $payId]);
                Auth::setFlash('success', "Payment record deleted.");
            } catch (\Throwable $e) {
                Auth::setFlash('danger', 'Failed to delete payment record.');
            }
        }
        header('Location: members.php?tab=payments');
        exit;
    }

    // 7. CHECK-IN ATTENDANCE
    if ($postAction === 'check_in') {
        $memberId = (int) ($_POST['member_id'] ?? 0);
        $method = Security::sanitizeString($_POST['method'] ?? 'Biometric Facial Gate', 50);
        $notes = Security::sanitizeString($_POST['notes'] ?? 'Floor training session', 255);

        if ($memberId > 0) {
            try {
                // Check if already checked in today without check-out
                $existing = Database::fetchOne("
                    SELECT `id` FROM `member_attendance`
                    WHERE `member_id` = :mid AND `check_out_time` IS NULL
                    ORDER BY `id` DESC LIMIT 1
                ", [':mid' => $memberId]);

                if ($existing) {
                    Auth::setFlash('warning', 'Athlete is already checked in on the training floor.');
                } else {
                    Database::execute("
                        INSERT INTO `member_attendance` (`member_id`, `check_in_time`, `method`, `notes`)
                        VALUES (:mid, NOW(), :mt, :nt)
                    ", [
                        ':mid' => $memberId,
                        ':mt'  => $method,
                        ':nt'  => $notes
                    ]);
                    Auth::setFlash('success', "Check-in recorded! Biometric gate unlocked.");
                }
            } catch (\Throwable $e) {
                Auth::setFlash('danger', 'Failed to record check-in.');
            }
        }
        header('Location: members.php?tab=attendance');
        exit;
    }

    // 8. CHECK-OUT ATTENDANCE
    if ($postAction === 'check_out') {
        $attId = (int) ($_POST['attendance_id'] ?? 0);
        if ($attId > 0) {
            try {
                Database::execute("
                    UPDATE `member_attendance`
                    SET `check_out_time` = NOW()
                    WHERE `id` = :id
                ", [':id' => $attId]);
                Auth::setFlash('success', "Athlete session closed. Biometric turnstile synced.");
            } catch (\Throwable $e) {
                Auth::setFlash('danger', 'Failed to check out athlete.');
            }
        }
        header('Location: members.php?tab=attendance');
        exit;
    }
}

// Fetch active plans for dropdowns
$plans = [];
try {
    $plans = Database::fetchAll("SELECT `id`, `name`, `slug`, `monthly_price`, `annual_monthly_price` FROM `membership_plans` WHERE `is_active` = 1 ORDER BY `sort_order` ASC");
} catch (\Throwable $e) {
    $plans = [];
}

// Fetch all members for quick-select lists
$allMembersList = [];
try {
    $allMembersList = Database::fetchAll("SELECT `id`, `membership_id`, `full_name`, `status`, `plan_name` FROM `members` ORDER BY `full_name` ASC");
} catch (\Throwable $e) {
    $allMembersList = [];
}

// Global Summary Counters
$metrics = [
    'total_members'    => 0,
    'active_members'   => 0,
    'expired_members'  => 0,
    'pending_members'  => 0,
    'total_revenue'    => 0.00,
    'today_attendance' => 0,
    'currently_on_floor' => 0
];

try {
    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `members`");
    $metrics['total_members'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `members` WHERE `status` = 'active'");
    $metrics['active_members'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `members` WHERE `status` = 'expired'");
    $metrics['expired_members'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `members` WHERE `status` = 'pending'");
    $metrics['pending_members'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT IFNULL(SUM(amount), 0) AS rev FROM `member_payments` WHERE `payment_status` = 'completed'");
    $metrics['total_revenue'] = (float) ($r['rev'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `member_attendance` WHERE DATE(`check_in_time`) = CURDATE()");
    $metrics['today_attendance'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `member_attendance` WHERE `check_out_time` IS NULL AND DATE(`check_in_time`) = CURDATE()");
    $metrics['currently_on_floor'] = (int) ($r['cnt'] ?? 0);
} catch (\Throwable $e) {
    error_log('[MEMBERS METRICS ERROR]: ' . $e->getMessage());
}

// Queries depending on current tab
$membersData = [];
$paymentsData = [];
$attendanceData = [];

// Filtering params
$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = Security::validateEnum($_GET['status'] ?? '', ['active', 'expired', 'pending']);

if ($currentTab === 'members') {
    $where = [];
    $params = [];
    if ($search !== '') {
        $where[] = "(`m`.`full_name` LIKE :s1 OR `m`.`membership_id` LIKE :s2 OR `m`.`email` LIKE :s3 OR `m`.`phone` LIKE :s4)";
        $wild = '%' . $search . '%';
        $params[':s1'] = $wild;
        $params[':s2'] = $wild;
        $params[':s3'] = $wild;
        $params[':s4'] = $wild;
    }
    if ($statusFilter) {
        $where[] = "`m`.`status` = :st";
        $params[':st'] = $statusFilter;
    }
    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    try {
        $membersData = Database::fetchAll("
            SELECT `m`.*,
                   (SELECT COUNT(*) FROM `member_payments` `p` WHERE `p`.`member_id` = `m`.`id`) AS payment_count,
                   (SELECT IFNULL(SUM(`p`.`amount`), 0) FROM `member_payments` `p` WHERE `p`.`member_id` = `m`.`id` AND `p`.`payment_status` = 'completed') AS total_paid
            FROM `members` `m`
            {$whereSql}
            ORDER BY `m`.`id` DESC
        ", $params);
    } catch (\Throwable $e) {
        error_log('[FETCH MEMBERS ERROR]: ' . $e->getMessage());
    }
} elseif ($currentTab === 'payments') {
    try {
        $paymentsData = Database::fetchAll("
            SELECT `p`.*, `m`.`full_name`, `m`.`membership_id`, `m`.`email`, `m`.`plan_name`
            FROM `member_payments` `p`
            JOIN `members` `m` ON `p`.`member_id` = `m`.`id`
            ORDER BY `p`.`payment_date` DESC, `p`.`id` DESC
        ");
    } catch (\Throwable $e) {
        error_log('[FETCH PAYMENTS ERROR]: ' . $e->getMessage());
    }
} elseif ($currentTab === 'attendance') {
    try {
        $attendanceData = Database::fetchAll("
            SELECT `a`.*, `m`.`full_name`, `m`.`membership_id`, `m`.`plan_name`, `m`.`status` AS member_status
            FROM `member_attendance` `a`
            JOIN `members` `m` ON `a`.`member_id` = `m`.`id`
            ORDER BY `a`.`check_in_time` DESC
            LIMIT 100
        ");
    } catch (\Throwable $e) {
        error_log('[FETCH ATTENDANCE ERROR]: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="adm-page-header">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h1 class="adm-page-title">
        <span>MEMBERS & ATTENDANCE</span>
        <span class="adm-badge active" style="font-size: 0.65rem;">MANAGEMENT</span>
      </h1>
      <p class="adm-page-subtitle">Manage gym member profiles, fee payments history, and daily attendance.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="adm-btn adm-btn-primary" onclick="openAdmModal('modalAddMember')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>+ Add Member</span>
      </button>
      <button type="button" class="adm-btn adm-btn-outline" onclick="openAdmModal('modalRecordPayment')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        <span>+ Log Fee Payment</span>
      </button>
      <button type="button" class="adm-btn adm-btn-outline" onclick="openAdmModal('modalCheckIn')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <span>+ Mark Check-In</span>
      </button>
    </div>
  </div>
</div>

<!-- Stat Counters Row -->
<div class="adm-grid adm-grid-4" style="margin-bottom: 1.5rem;">
  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Active Members</span>
      <div class="adm-stat-icon" style="color: var(--adm-lime);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
      </div>
    </div>
    <div class="adm-stat-value text-lime"><?= number_format($metrics['active_members']) ?></div>
    <div class="adm-stat-footer">
      <span class="adm-stat-badge lime"><?= $metrics['total_members'] ?> TOTAL MEMBERS</span>
      <span>&bull;</span>
      <span class="text-muted" style="font-size: 0.72rem;"><?= $metrics['expired_members'] ?> Expired</span>
    </div>
  </div>

  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Fees Collected</span>
      <div class="adm-stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
      </div>
    </div>
    <div class="adm-stat-value">$<?= number_format($metrics['total_revenue'], 2) ?></div>
    <div class="adm-stat-footer">
      <span class="adm-stat-badge lime">TOTAL REVENUE</span>
    </div>
  </div>

  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Today's Attendance</span>
      <div class="adm-stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      </div>
    </div>
    <div class="adm-stat-value"><?= number_format($metrics['today_attendance']) ?></div>
    <div class="adm-stat-footer">
      <span class="adm-stat-badge orange"><?= $metrics['currently_on_floor'] ?> CURRENTLY IN GYM</span>
    </div>
  </div>

  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Pending Approvals</span>
      <div class="adm-stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
      </div>
    </div>
    <div class="adm-stat-value"><?= number_format($metrics['pending_members']) ?></div>
    <div class="adm-stat-footer">
      <span class="adm-stat-badge <?= $metrics['pending_members'] > 0 ? 'orange' : 'lime' ?>"><?= $metrics['pending_members'] > 0 ? 'NEEDS REVIEW' : 'ALL CLEAR' ?></span>
    </div>
  </div>
</div>

<!-- Tabs Navigation -->
<div class="adm-card p-0" style="margin-bottom: 1.5rem; overflow: hidden;">
  <div class="d-flex flex-wrap border-bottom" style="border-color: var(--adm-border) !important; background: var(--adm-surface);">
    <a href="members.php?tab=members" class="adm-tab-link <?= $currentTab === 'members' ? 'active' : '' ?>">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
      <span>Members Directory (<?= $metrics['total_members'] ?>)</span>
    </a>
    <a href="members.php?tab=payments" class="adm-tab-link <?= $currentTab === 'payments' ? 'active' : '' ?>">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
      <span>Payment History ($<?= number_format($metrics['total_revenue']) ?>)</span>
    </a>
    <a href="members.php?tab=attendance" class="adm-tab-link <?= $currentTab === 'attendance' ? 'active' : '' ?>">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      <span>Daily Attendance (<?= $metrics['today_attendance'] ?> today)</span>
    </a>
  </div>

  <!-- ================= TAB 1: MEMBERS DIRECTORY ================= -->
  <?php if ($currentTab === 'members'): ?>
    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3" style="border-color: var(--adm-border) !important; background: var(--adm-surface-2);">
      <!-- Filter Form -->
      <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <input type="hidden" name="tab" value="members">
        <input type="text" name="search" class="adm-input" placeholder="Search name, ID, phone..." value="<?= e($search) ?>" style="width: 220px;">
        <select name="status" class="adm-select" style="width: 150px;">
          <option value="">All Statuses</option>
          <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="expired" <?= $statusFilter === 'expired' ? 'selected' : '' ?>>Expired</option>
          <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
        </select>
        <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm">Filter</button>
        <?php if ($search !== '' || $statusFilter !== ''): ?>
          <a href="members.php?tab=members" class="adm-btn adm-btn-outline adm-btn-sm">Reset</a>
        <?php endif; ?>
      </form>

      <span class="text-muted" style="font-size: 0.8rem; font-family: var(--adm-font-mono);">
        Showing <?= count($membersData) ?> members
      </span>
    </div>

    <div class="adm-table-responsive">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Member ID</th>
            <th>Member Name</th>
            <th>Plan</th>
            <th>Billing Cycle</th>
            <th>Start Date</th>
            <th>Expiry Date</th>
            <th>Status</th>
            <th>Fees Paid</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($membersData)): ?>
            <tr>
              <td colspan="9" class="adm-empty-state">
                No member profiles found matching current criteria.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($membersData as $m): ?>
              <?php
                $isExpired = ($m['status'] === 'expired' || strtotime($m['expiry_date']) < time());
                $statusClass = $m['status'] === 'active' ? 'active' : ($m['status'] === 'expired' ? 'danger' : 'warning');
              ?>
              <tr>
                <td>
                  <span class="adm-mono text-lime fw-bold"><?= e($m['membership_id']) ?></span>
                </td>
                <td>
                  <div class="fw-bold text-white"><?= e($m['full_name']) ?></div>
                  <div class="text-muted" style="font-size: 0.78rem;"><?= e($m['email']) ?> &bull; <?= e($m['phone']) ?></div>
                </td>
                <td>
                  <span class="adm-badge" style="background: rgba(255,255,255,0.06); color: var(--adm-white);"><?= e($m['plan_name']) ?></span>
                </td>
                <td style="text-transform: capitalize; font-size: 0.85rem; color: var(--adm-silver);">
                  <?= e($m['billing_cycle']) ?>
                </td>
                <td class="adm-mono" style="font-size: 0.8rem;">
                  <?= e($m['start_date']) ?>
                </td>
                <td class="adm-mono" style="font-size: 0.8rem; <?= $isExpired ? 'color: var(--adm-danger);' : '' ?>">
                  <?= e($m['expiry_date']) ?>
                </td>
                <td>
                  <span class="adm-badge <?= $statusClass ?>" style="font-size: 0.7rem;">
                    <?= strtoupper(e($m['status'])) ?>
                  </span>
                </td>
                <td>
                  <span class="adm-mono text-white fw-bold">$<?= number_format((float) $m['total_paid'], 2) ?></span>
                  <div class="text-muted" style="font-size: 0.7rem;"><?= (int) $m['payment_count'] ?> txn(s)</div>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <!-- Quick View Profile -->
                    <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" title="View Profile Details" onclick='viewMember(<?= json_encode($m) ?>)'>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>

                    <!-- Edit Profile -->
                    <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" title="Edit Profile" onclick='editMember(<?= json_encode($m) ?>)'>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>

                    <!-- Delete Profile Form -->
                    <form method="POST" onsubmit="return confirm('Permanently remove member <?= e($m['membership_id']) ?> and related records?');" style="display:inline;">
                      <?= Security::csrfField() ?>
                      <input type="hidden" name="action" value="delete_member">
                      <input type="hidden" name="member_id" value="<?= (int) $m['id'] ?>">
                      <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" title="Delete Profile">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <!-- ================= TAB 2: PAYMENT RECORDS ================= -->
  <?php if ($currentTab === 'payments'): ?>
    <div class="adm-table-responsive">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Receipt / Txn ID</th>
            <th>Member Details</th>
            <th>Plan</th>
            <th>Amount</th>
            <th>Payment Mode</th>
            <th>Status</th>
            <th>Date</th>
            <th>Notes</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($paymentsData)): ?>
            <tr>
              <td colspan="9" class="adm-empty-state">
                No fee payment records found. Click "+ Log Fee Payment" to record one.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($paymentsData as $p): ?>
              <tr>
                <td>
                  <span class="adm-mono text-lime fw-bold"><?= e($p['transaction_id']) ?></span>
                </td>
                <td>
                  <div class="fw-bold text-white"><?= e($p['full_name']) ?></div>
                  <div class="text-muted" style="font-size: 0.76rem; font-family: var(--adm-font-mono);"><?= e($p['membership_id']) ?></div>
                </td>
                <td>
                  <span class="adm-badge" style="background: rgba(255,255,255,0.06);"><?= e($p['plan_name']) ?></span>
                </td>
                <td>
                  <span class="adm-mono text-white fw-bold" style="font-size: 0.95rem;">$<?= number_format((float) $p['amount'], 2) ?></span>
                </td>
                <td>
                  <span class="adm-badge" style="background: rgba(0, 229, 255, 0.1); color: var(--adm-lime); border: 1px solid rgba(0, 229, 255, 0.25);">
                    <?= e($p['payment_method']) ?>
                  </span>
                </td>
                <td>
                  <span class="adm-badge <?= $p['payment_status'] === 'completed' ? 'lime' : ($p['payment_status'] === 'pending' ? 'orange' : 'danger') ?>">
                    <?= strtoupper(e($p['payment_status'])) ?>
                  </span>
                </td>
                <td class="adm-mono" style="font-size: 0.8rem;">
                  <?= e($p['payment_date']) ?>
                </td>
                <td style="font-size: 0.8rem; color: var(--adm-silver);">
                  <?= e($p['notes'] ?: '—') ?>
                </td>
                <td class="text-end">
                  <form method="POST" onsubmit="return confirm('Remove payment transaction record <?= e($p['transaction_id']) ?>?');" style="display:inline;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="delete_payment">
                    <input type="hidden" name="payment_id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm" title="Delete Payment Record">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <!-- ================= TAB 3: FLOOR ATTENDANCE RECORDS ================= -->
  <?php if ($currentTab === 'attendance'): ?>
    <div class="adm-table-responsive">
      <table class="adm-table">
        <thead>
          <tr>
            <th>Entry #</th>
            <th>Member Name</th>
            <th>Member ID</th>
            <th>Plan</th>
            <th>Check-In Time</th>
            <th>Check-Out Time</th>
            <th>Status</th>
            <th>Entry Method</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($attendanceData)): ?>
            <tr>
              <td colspan="9" class="adm-empty-state">
                No attendance entries logged for today yet. Click "+ Mark Check-In" to record attendance.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($attendanceData as $a): ?>
              <?php $isOnFloor = ($a['check_out_time'] === null); ?>
              <tr>
                <td class="adm-mono text-muted">#<?= (int) $a['id'] ?></td>
                <td>
                  <div class="fw-bold text-white"><?= e($a['full_name']) ?></div>
                </td>
                <td>
                  <span class="adm-mono text-lime fw-bold"><?= e($a['membership_id']) ?></span>
                </td>
                <td>
                  <span class="adm-badge" style="background: rgba(255,255,255,0.06);"><?= e($a['plan_name']) ?></span>
                </td>
                <td class="adm-mono" style="font-size: 0.8rem; color: var(--adm-white);">
                  <?= date('M d, Y &bull; H:i:s', strtotime($a['check_in_time'])) ?>
                </td>
                <td class="adm-mono" style="font-size: 0.8rem;">
                  <?= $a['check_out_time'] ? date('M d, Y &bull; H:i:s', strtotime($a['check_out_time'])) : '<span class="text-muted">— Currently Inside —</span>' ?>
                </td>
                <td>
                  <?php if ($isOnFloor): ?>
                    <span class="adm-badge lime">INSIDE GYM</span>
                  <?php else: ?>
                    <span class="adm-badge" style="background: rgba(255,255,255,0.08); color: var(--adm-silver);">CHECKED OUT</span>
                  <?php endif; ?>
                </td>
                <td style="font-size: 0.8rem; color: var(--adm-silver);">
                  <?= e($a['method']) ?>
                </td>
                <td class="text-end">
                  <?php if ($isOnFloor): ?>
                    <form method="POST" style="display:inline;">
                      <?= Security::csrfField() ?>
                      <input type="hidden" name="action" value="check_out">
                      <input type="hidden" name="attendance_id" value="<?= (int) $a['id'] ?>">
                      <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm">
                        <span>Check Out</span>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted" style="font-size: 0.75rem;">Done</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- ================= MODAL: ADD MEMBER ================= -->
<div id="modalAddMember" class="adm-modal-overlay">
  <div class="adm-modal-box" style="max-width: 640px;">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title">ADD NEW GYM MEMBER</h3>
      <button type="button" class="adm-modal-close" onclick="closeAdmModal('modalAddMember')">&times;</button>
    </div>
    <form method="POST">
      <?= Security::csrfField() ?>
      <input type="hidden" name="action" value="create_member">

      <div class="adm-modal-body">
        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Full Name <span class="text-lime">*</span></label>
            <input type="text" name="full_name" class="adm-input" required placeholder="e.g. Rahul Sharma">
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Membership ID (Auto or Custom)</label>
            <input type="text" name="membership_id" class="adm-input" placeholder="Leave empty for auto-generated">
          </div>
        </div>

        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Email Address <span class="text-lime">*</span></label>
            <input type="email" name="email" class="adm-input" required placeholder="member@gmail.com">
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Phone Number <span class="text-lime">*</span></label>
            <input type="text" name="phone" class="adm-input" required placeholder="e.g. 9876543210">
          </div>
        </div>

        <div class="adm-grid adm-grid-3 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Membership Plan <span class="text-lime">*</span></label>
            <select name="plan_id" class="adm-select" required>
              <?php foreach ($plans as $p): ?>
                <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> ($<?= (int)$p['monthly_price'] ?>/mo)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Billing Cycle</label>
            <select name="billing_cycle" class="adm-select">
              <option value="monthly">Monthly</option>
              <option value="annual">Annual (Save 20%)</option>
            </select>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Status</label>
            <select name="status" class="adm-select">
              <option value="active" selected>Active</option>
              <option value="pending">Pending</option>
              <option value="expired">Expired</option>
            </select>
          </div>
        </div>

        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Start Date</label>
            <input type="date" name="start_date" class="adm-input" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Expiry Date</label>
            <input type="date" name="expiry_date" class="adm-input" value="<?= date('Y-m-d', strtotime('+1 month')) ?>" required>
          </div>
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Emergency Contact (Optional)</label>
          <input type="text" name="emergency_contact" class="adm-input" placeholder="Contact Person Name & Phone">
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Notes (Locker, Medical info, Goals)</label>
          <textarea name="notes" class="adm-textarea" rows="2" placeholder="Any special instructions or member notes..."></textarea>
        </div>

        <!-- Initial Payment Log Option -->
        <div class="p-3 mb-2" style="background: rgba(0, 229, 255, 0.05); border: 1px solid var(--adm-border); border-radius: 4px;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-lime fw-bold" style="font-size: 0.8rem; font-family: var(--adm-font-mono);">INITIAL FEE PAYMENT (OPTIONAL)</span>
            <span class="text-muted" style="font-size: 0.72rem;">Records first payment</span>
          </div>
          <div class="adm-grid adm-grid-2">
            <div>
              <label class="adm-label" style="font-size: 0.75rem;">Payment Amount ($)</label>
              <input type="number" step="0.01" name="initial_payment_amount" class="adm-input" placeholder="e.g. 99.00">
            </div>
            <div>
              <label class="adm-label" style="font-size: 0.75rem;">Payment Method</label>
              <select name="initial_payment_method" class="adm-select">
                <option value="Cash / Reception">Cash at Reception</option>
                <option value="UPI">UPI / GPay / PhonePe</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Debit Card">Debit Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="adm-modal-footer">
        <button type="button" class="adm-btn adm-btn-outline" onclick="closeAdmModal('modalAddMember')">Cancel</button>
        <button type="submit" class="adm-btn adm-btn-primary">Save Member</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL: EDIT MEMBER ================= -->
<div id="modalEditMember" class="adm-modal-overlay">
  <div class="adm-modal-box" style="max-width: 640px;">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title">EDIT MEMBER DETAILS</h3>
      <button type="button" class="adm-modal-close" onclick="closeAdmModal('modalEditMember')">&times;</button>
    </div>
    <form method="POST">
      <?= Security::csrfField() ?>
      <input type="hidden" name="action" value="edit_member">
      <input type="hidden" id="editMemberId" name="member_id" value="0">

      <div class="adm-modal-body">
        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Full Name <span class="text-lime">*</span></label>
            <input type="text" id="editFullName" name="full_name" class="adm-input" required>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Membership ID (Locked)</label>
            <input type="text" id="editMembershipCode" class="adm-input" readonly style="background: rgba(0,0,0,0.5); color: var(--adm-lime);">
          </div>
        </div>

        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Email Address <span class="text-lime">*</span></label>
            <input type="email" id="editEmail" name="email" class="adm-input" required>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Phone Number <span class="text-lime">*</span></label>
            <input type="text" id="editPhone" name="phone" class="adm-input" required>
          </div>
        </div>

        <div class="adm-grid adm-grid-3 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Membership Plan <span class="text-lime">*</span></label>
            <select id="editPlanId" name="plan_id" class="adm-select" required>
              <?php foreach ($plans as $p): ?>
                <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> ($<?= (int)$p['monthly_price'] ?>/mo)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Billing Cycle</label>
            <select id="editBillingCycle" name="billing_cycle" class="adm-select">
              <option value="monthly">Monthly</option>
              <option value="annual">Annual (Save 20%)</option>
            </select>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Status</label>
            <select id="editStatus" name="status" class="adm-select">
              <option value="active">Active</option>
              <option value="pending">Pending</option>
              <option value="expired">Expired</option>
            </select>
          </div>
        </div>

        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Start Date</label>
            <input type="date" id="editStartDate" name="start_date" class="adm-input" required>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Expiry Date</label>
            <input type="date" id="editExpiryDate" name="expiry_date" class="adm-input" required>
          </div>
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Emergency Contact (Optional)</label>
          <input type="text" id="editEmergency" name="emergency_contact" class="adm-input">
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Notes</label>
          <textarea id="editNotes" name="notes" class="adm-textarea" rows="2"></textarea>
        </div>
      </div>

      <div class="adm-modal-footer">
        <button type="button" class="adm-btn adm-btn-outline" onclick="closeAdmModal('modalEditMember')">Cancel</button>
        <button type="submit" class="adm-btn adm-btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL: VIEW MEMBER PROFILE ================= -->
<div id="modalViewMember" class="adm-modal-overlay">
  <div class="adm-modal-box" style="max-width: 600px;">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title">MEMBER PROFILE DETAILS</h3>
      <button type="button" class="adm-modal-close" onclick="closeAdmModal('modalViewMember')">&times;</button>
    </div>
    <div class="adm-modal-body">
      <div class="d-flex align-items-center justify-content-between p-3 mb-3" style="background: rgba(0, 229, 255, 0.08); border: 1px solid rgba(0, 229, 255, 0.3); border-radius: 4px;">
        <div>
          <span class="adm-mono text-lime fw-bold" id="viewMemberCode" style="font-size: 1.2rem;">DM-2026-0000</span>
          <h4 id="viewMemberName" class="text-white mt-1 mb-0" style="font-size: 1.25rem;">Member Name</h4>
        </div>
        <span id="viewMemberStatusBadge" class="adm-badge lime" style="font-size: 0.8rem;">ACTIVE</span>
      </div>

      <div class="adm-grid adm-grid-2 mb-3">
        <div class="p-2 border" style="border-color: var(--adm-border) !important;">
          <div class="text-muted" style="font-size: 0.72rem; font-family: var(--adm-font-mono);">EMAIL ADDRESS</div>
          <div id="viewMemberEmail" class="text-white fw-bold">member@gmail.com</div>
        </div>
        <div class="p-2 border" style="border-color: var(--adm-border) !important;">
          <div class="text-muted" style="font-size: 0.72rem; font-family: var(--adm-font-mono);">PHONE NUMBER</div>
          <div id="viewMemberPhone" class="text-white fw-bold">0000000000</div>
        </div>
        <div class="p-2 border" style="border-color: var(--adm-border) !important;">
          <div class="text-muted" style="font-size: 0.72rem; font-family: var(--adm-font-mono);">MEMBERSHIP PLAN</div>
          <div id="viewMemberPlan" class="text-lime fw-bold">Standard Plan</div>
        </div>
        <div class="p-2 border" style="border-color: var(--adm-border) !important;">
          <div class="text-muted" style="font-size: 0.72rem; font-family: var(--adm-font-mono);">VALID DURATION</div>
          <div id="viewMemberDates" class="text-white" style="font-size: 0.82rem;">2026-01-01 to 2026-04-01</div>
        </div>
      </div>

      <div class="mb-3">
        <div class="text-muted" style="font-size: 0.72rem; font-family: var(--adm-font-mono);">EMERGENCY CONTACT</div>
        <div id="viewMemberEmergency" class="text-white mt-1">—</div>
      </div>

      <div class="mb-3">
        <div class="text-muted" style="font-size: 0.72rem; font-family: var(--adm-font-mono);">MEMBER NOTES</div>
        <div id="viewMemberNotes" class="text-silver mt-1" style="font-size: 0.85rem;">—</div>
      </div>
    </div>
    <div class="adm-modal-footer">
      <button type="button" class="adm-btn adm-btn-outline" onclick="closeAdmModal('modalViewMember')">Close</button>
    </div>
  </div>
</div>

<!-- ================= MODAL: RECORD PAYMENT ================= -->
<div id="modalRecordPayment" class="adm-modal-overlay">
  <div class="adm-modal-box" style="max-width: 500px;">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title">RECORD FEE PAYMENT</h3>
      <button type="button" class="adm-modal-close" onclick="closeAdmModal('modalRecordPayment')">&times;</button>
    </div>
    <form method="POST">
      <?= Security::csrfField() ?>
      <input type="hidden" name="action" value="record_payment">

      <div class="adm-modal-body">
        <div class="adm-form-group mb-3">
          <label class="adm-label">Select Gym Member <span class="text-lime">*</span></label>
          <select name="member_id" class="adm-select" required>
            <option value="">Choose registered member</option>
            <?php foreach ($allMembersList as $mb): ?>
              <option value="<?= (int) $mb['id'] ?>"><?= e($mb['full_name']) ?> (<?= e($mb['membership_id']) ?> — <?= e($mb['plan_name']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Amount ($) <span class="text-lime">*</span></label>
            <input type="number" step="0.01" name="amount" class="adm-input" placeholder="e.g. 99.00" required>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Payment Method</label>
            <select name="payment_method" class="adm-select">
              <option value="Cash / Reception">Cash at Reception</option>
              <option value="UPI">UPI / GPay / PhonePe</option>
              <option value="Credit Card">Credit Card</option>
              <option value="Debit Card">Debit Card</option>
              <option value="Bank Transfer">Bank Transfer</option>
            </select>
          </div>
        </div>

        <div class="adm-grid adm-grid-2 mb-3">
          <div class="adm-form-group">
            <label class="adm-label">Payment Status</label>
            <select name="payment_status" class="adm-select">
              <option value="completed" selected>Completed</option>
              <option value="pending">Pending</option>
              <option value="refunded">Refunded</option>
            </select>
          </div>
          <div class="adm-form-group">
            <label class="adm-label">Payment Date</label>
            <input type="date" name="payment_date" class="adm-input" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Receipt / Transaction ID (Auto-generated if empty)</label>
          <input type="text" name="transaction_id" class="adm-input" placeholder="Leave empty for auto ID">
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Notes (Optional)</label>
          <input type="text" name="notes" class="adm-input" placeholder="e.g. Monthly fee for March">
        </div>
      </div>

      <div class="adm-modal-footer">
        <button type="button" class="adm-btn adm-btn-outline" onclick="closeAdmModal('modalRecordPayment')">Cancel</button>
        <button type="submit" class="adm-btn adm-btn-primary">Save Payment Record</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL: QUICK CHECK-IN ================= -->
<div id="modalCheckIn" class="adm-modal-overlay">
  <div class="adm-modal-box" style="max-width: 480px;">
    <div class="adm-modal-header">
      <h3 class="adm-modal-title">MEMBER CHECK-IN</h3>
      <button type="button" class="adm-modal-close" onclick="closeAdmModal('modalCheckIn')">&times;</button>
    </div>
    <form method="POST">
      <?= Security::csrfField() ?>
      <input type="hidden" name="action" value="check_in">

      <div class="adm-modal-body">
        <div class="adm-form-group mb-3">
          <label class="adm-label">Select Member <span class="text-lime">*</span></label>
          <select name="member_id" class="adm-select" required>
            <option value="">Select registered member</option>
            <?php foreach ($allMembersList as $mb): ?>
              <?php $isAct = ($mb['status'] === 'active'); ?>
              <option value="<?= (int) $mb['id'] ?>" <?= !$isAct ? 'disabled' : '' ?>>
                <?= e($mb['full_name']) ?> (<?= e($mb['membership_id']) ?>) [<?= strtoupper(e($mb['status'])) ?>]
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Check-In Method</label>
          <select name="method" class="adm-select">
            <option value="Front Desk Manual">Front Desk Manual Check-In</option>
            <option value="Biometric Scanner">Biometric / Fingerprint Scanner</option>
            <option value="QR Code Scan">QR Code / Mobile Pass</option>
          </select>
        </div>

        <div class="adm-form-group mb-3">
          <label class="adm-label">Notes (Optional)</label>
          <input type="text" name="notes" class="adm-input" placeholder="e.g. Morning Workout Session">
        </div>
      </div>

      <div class="adm-modal-footer">
        <button type="button" class="adm-btn adm-btn-outline" onclick="closeAdmModal('modalCheckIn')">Cancel</button>
        <button type="submit" class="adm-btn adm-btn-primary">Confirm Check-In</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAdmModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}

function closeAdmModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

function editMember(m) {
  document.getElementById('editMemberId').value = m.id;
  document.getElementById('editMembershipCode').value = m.membership_id;
  document.getElementById('editFullName').value = m.full_name;
  document.getElementById('editEmail').value = m.email;
  document.getElementById('editPhone').value = m.phone;
  document.getElementById('editPlanId').value = m.plan_id;
  document.getElementById('editBillingCycle').value = m.billing_cycle || 'monthly';
  document.getElementById('editStatus').value = m.status;
  document.getElementById('editStartDate').value = m.start_date;
  document.getElementById('editExpiryDate').value = m.expiry_date;
  document.getElementById('editEmergency').value = m.emergency_contact || '';
  document.getElementById('editNotes').value = m.notes || '';
  openAdmModal('modalEditMember');
}

function viewMember(m) {
  document.getElementById('viewMemberCode').textContent = m.membership_id;
  document.getElementById('viewMemberName').textContent = m.full_name;
  document.getElementById('viewMemberEmail').textContent = m.email;
  document.getElementById('viewMemberPhone').textContent = m.phone;
  document.getElementById('viewMemberPlan').textContent = m.plan_name || 'Standard Plan';
  document.getElementById('viewMemberDates').textContent = m.start_date + ' to ' + m.expiry_date;
  document.getElementById('viewMemberEmergency').textContent = m.emergency_contact || 'None specified';
  document.getElementById('viewMemberNotes').textContent = m.notes || 'No notes recorded.';

  const badge = document.getElementById('viewMemberStatusBadge');
  badge.textContent = m.status.toUpperCase();
  badge.className = 'adm-badge ' + (m.status === 'active' ? 'lime' : (m.status === 'expired' ? 'danger' : 'warning'));

  openAdmModal('modalViewMember');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
