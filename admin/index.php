<?php
/**
 * 24HRS FITNESS — Admin Dashboard
 * Clean, Simple & Comprehensive Management Overview
 */

declare(strict_types=1);

$pageTitle = 'Dashboard Overview';

require_once __DIR__ . '/includes/header.php';

use DarkMatter\Database;

// Fetch Dashboard Metrics
$metrics = [
    'total_enquiries'     => 0,
    'pending_enquiries'   => 0,
    'total_applications'  => 0,
    'pending_applications'=> 0,
    'total_members'       => 0,
    'active_members'      => 0,
    'total_revenue'       => 0.00,
    'today_attendance'    => 0,
    'currently_on_floor'  => 0,
    'active_plans'        => 0,
    'total_trainers'      => 0,
    'total_programs'      => 0,
    'total_gallery'       => 0,
];

try {
    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `enquiries`");
    $metrics['total_enquiries'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `enquiries` WHERE `status` = 'pending'");
    $metrics['pending_enquiries'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `membership_applications`");
    $metrics['total_applications'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `membership_applications` WHERE `status` = 'pending'");
    $metrics['pending_applications'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `members`");
    $metrics['total_members'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `members` WHERE `status` = 'active'");
    $metrics['active_members'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT IFNULL(SUM(`amount`), 0) AS rev FROM `member_payments` WHERE `payment_status` = 'completed'");
    $metrics['total_revenue'] = (float) ($r['rev'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `member_attendance` WHERE DATE(`check_in_time`) = CURDATE()");
    $metrics['today_attendance'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `member_attendance` WHERE `check_out_time` IS NULL AND DATE(`check_in_time`) = CURDATE()");
    $metrics['currently_on_floor'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `membership_plans` WHERE `is_active` = 1");
    $metrics['active_plans'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `trainers` WHERE `is_active` = 1");
    $metrics['total_trainers'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `programs` WHERE `is_active` = 1");
    $metrics['total_programs'] = (int) ($r['cnt'] ?? 0);

    $r = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `gallery_images` WHERE `is_active` = 1");
    $metrics['total_gallery'] = (int) ($r['cnt'] ?? 0);

    // Latest 5 Enquiries
    $recentEnquiries = Database::fetchAll(
        "SELECT `id`, `full_name`, `email`, `phone`, `inquiry_type`, `status`, `created_at`
         FROM `enquiries`
         ORDER BY `id` DESC
         LIMIT 5"
    );

    // Latest 5 Members
    $recentMembers = Database::fetchAll(
        "SELECT `id`, `membership_id`, `full_name`, `email`, `plan_name`, `billing_cycle`, `status`, `created_at`
         FROM `members`
         ORDER BY `id` DESC
         LIMIT 5"
    );
} catch (\Throwable $e) {
    error_log('[DASHBOARD QUERY ERROR]: ' . $e->getMessage());
    $recentEnquiries = [];
    $recentMembers = [];
}
?>

<div class="adm-page-header">
  <h1 class="adm-page-title">
    <span>GYM DASHBOARD</span>
    <span class="adm-badge active" style="font-size: 0.65rem;">ONLINE</span>
  </h1>
  <p class="adm-page-subtitle">Welcome to your gym management portal. View inquiries, active members, revenue, and daily attendance.</p>
</div>

<!-- Primary Stats Grid -->
<div class="adm-grid adm-grid-4" style="margin-bottom: 1.5rem;">
  <!-- Total Enquiries Card -->
  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Customer Inquiries</span>
      <div class="adm-stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
      </div>
    </div>
    <div class="adm-stat-value"><?= number_format($metrics['total_enquiries']) ?></div>
    <div class="adm-stat-footer">
      <?php if ($metrics['pending_enquiries'] > 0): ?>
        <span class="adm-stat-badge orange"><?= $metrics['pending_enquiries'] ?> PENDING</span>
      <?php else: ?>
        <span class="adm-stat-badge lime">ALL ANSWERED</span>
      <?php endif; ?>
      <span>&bull;</span>
      <a href="enquiries.php" style="font-size: 0.72rem;">View All &rarr;</a>
    </div>
  </div>

  <!-- Active Members Card -->
  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Active Members</span>
      <div class="adm-stat-icon" style="color: var(--adm-lime);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
      </div>
    </div>
    <div class="adm-stat-value text-lime"><?= number_format($metrics['active_members']) ?></div>
    <div class="adm-stat-footer">
      <span class="adm-stat-badge lime"><?= $metrics['total_members'] ?> TOTAL REGISTERED</span>
      <span>&bull;</span>
      <a href="members.php" style="font-size: 0.72rem;">Members List &rarr;</a>
    </div>
  </div>

  <!-- Revenue Summary Card -->
  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Fees Collected</span>
      <div class="adm-stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
      </div>
    </div>
    <div class="adm-stat-value">₹<?= number_format($metrics['total_revenue'], 2) ?></div>
    <div class="adm-stat-footer">
      <span class="adm-stat-badge lime">VERIFIED REVENUE</span>
      <span>&bull;</span>
      <a href="members.php?tab=payments" style="font-size: 0.72rem;">Payment History &rarr;</a>
    </div>
  </div>

  <!-- Floor Attendance Card -->
  <div class="adm-card">
    <div class="adm-stat-header">
      <span class="adm-stat-title">Today's Attendance</span>
      <div class="adm-stat-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      </div>
    </div>
    <div class="adm-stat-value"><?= number_format($metrics['today_attendance']) ?></div>
    <div class="adm-stat-footer">
      <span class="adm-stat-badge orange"><?= $metrics['currently_on_floor'] ?> IN GYM RIGHT NOW</span>
      <span>&bull;</span>
      <a href="members.php?tab=attendance" style="font-size: 0.72rem;">Attendance Log &rarr;</a>
    </div>
  </div>
</div>

<!-- Quick Shortcuts -->
<div class="adm-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
    <div style="font-family: var(--adm-font-mono); font-size: 0.75rem; color: var(--adm-silver); text-transform: uppercase;">
      <strong style="color: var(--adm-lime);">&bull; QUICK ACTIONS:</strong> Frequently used gym tools
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
      <a href="members.php" class="adm-btn adm-btn-primary adm-btn-sm">+ Add New Member</a>
      <a href="members.php?tab=payments" class="adm-btn adm-btn-outline adm-btn-sm">+ Log Fee Payment</a>
      <a href="members.php?tab=attendance" class="adm-btn adm-btn-outline adm-btn-sm">+ Mark Check-In</a>
      <a href="plans.php" class="adm-btn adm-btn-outline adm-btn-sm">Plans (<?= $metrics['active_plans'] ?>)</a>
      <a href="enquiries.php?status=pending" class="adm-btn adm-btn-lime adm-btn-sm">Pending Leads (<?= $metrics['pending_enquiries'] ?>)</a>
    </div>
  </div>
</div>

<!-- Recent Activity Tables (2 Columns) -->
<div class="adm-grid adm-grid-2">
  <!-- Recent Enquiries -->
  <div class="adm-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h3 style="font-size: 1.15rem; margin: 0;">LATEST WEBSITE INQUIRIES</h3>
      <a href="enquiries.php" class="adm-btn adm-btn-outline adm-btn-sm">View All &rarr;</a>
    </div>

    <?php if (empty($recentEnquiries)): ?>
      <div class="adm-empty" style="padding: 2rem 1rem;">
        <div class="adm-empty-title">No Inquiries Found</div>
        <div class="adm-empty-text">No customer inquiries have been submitted yet.</div>
      </div>
    <?php else: ?>
      <div class="adm-table-container" style="border: none; margin-bottom: 0;">
        <table class="adm-table">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Inquiry Type</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentEnquiries as $enq): ?>
              <tr>
                <td>
                  <div style="font-weight: 600; color: var(--adm-white);"><?= e($enq['full_name']) ?></div>
                  <div style="font-size: 0.72rem; color: var(--adm-silver-dim); font-family: var(--adm-font-mono);"><?= e($enq['email']) ?></div>
                </td>
                <td>
                  <span class="adm-mono" style="font-size: 0.75rem; text-transform: uppercase; color: var(--adm-silver);">
                    <?= e(str_replace('-', ' ', $enq['inquiry_type'])) ?>
                  </span>
                </td>
                <td>
                  <span class="adm-badge <?= e($enq['status']) ?>">
                    <?= e($enq['status']) ?>
                  </span>
                </td>
                <td style="font-family: var(--adm-font-mono); font-size: 0.72rem; color: var(--adm-silver-dim); white-space: nowrap;">
                  <?= date('M d, H:i', strtotime($enq['created_at'])) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Recent Registered Members -->
  <div class="adm-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h3 style="font-size: 1.15rem; margin: 0;">RECENTLY JOINED MEMBERS</h3>
      <a href="members.php" class="adm-btn adm-btn-outline adm-btn-sm">View All Members &rarr;</a>
    </div>

    <?php if (empty($recentMembers)): ?>
      <div class="adm-empty" style="padding: 2rem 1rem;">
        <div class="adm-empty-title">No Members Enrolled</div>
        <div class="adm-empty-text">No gym members have enrolled yet.</div>
      </div>
    <?php else: ?>
      <div class="adm-table-container" style="border: none; margin-bottom: 0;">
        <table class="adm-table">
          <thead>
            <tr>
              <th>Member ID</th>
              <th>Member Name</th>
              <th>Membership Plan</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentMembers as $rm): ?>
              <tr>
                <td>
                  <span class="adm-mono text-lime fw-bold" style="font-size: 0.8rem;"><?= e($rm['membership_id']) ?></span>
                </td>
                <td>
                  <div style="font-weight: 600; color: var(--adm-white);"><?= e($rm['full_name']) ?></div>
                  <div style="font-size: 0.72rem; color: var(--adm-silver-dim); font-family: var(--adm-font-mono);"><?= e($rm['email']) ?></div>
                </td>
                <td>
                  <span style="font-family: var(--adm-font-heading); font-size: 0.95rem; color: var(--adm-white);">
                    <?= e($rm['plan_name']) ?>
                  </span>
                  <div style="font-size: 0.7rem; color: var(--adm-silver-dim); text-transform: uppercase;">
                    <?= e($rm['billing_cycle']) ?>
                  </div>
                </td>
                <td>
                  <span class="adm-badge <?= $rm['status'] === 'active' ? 'lime' : ($rm['status'] === 'expired' ? 'danger' : 'warning') ?>">
                    <?= strtoupper(e($rm['status'])) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
