<?php
/**
 * DARK MATTER — Elite Fitness
 * Admin Header & Sidebar Layout Partial
 */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__, 2) . '/includes/Database.php';
require_once dirname(__DIR__, 2) . '/includes/Security.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';

use DarkMatter\Admin\Auth;
use DarkMatter\Database;
use DarkMatter\Security;

// Enforce authentication
Auth::requireAuth();

$currentUser = Auth::user();
$csrfToken = Security::getCsrfToken();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');

// Fetch telemetry counters for navigation badges
$pendingEnquiriesCount = 0;
$pendingApplicationsCount = 0;

try {
    $enqRow = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `enquiries` WHERE `status` = 'pending'");
    $pendingEnquiriesCount = (int) ($enqRow['cnt'] ?? 0);

    $appRow = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `membership_applications` WHERE `status` = 'pending'");
    $pendingApplicationsCount = (int) ($appRow['cnt'] ?? 0);
} catch (\Throwable $e) {
    // Graceful fallback if database connection hiccup
    error_log('[ADMIN NAV COUNTER ERROR]: ' . $e->getMessage());
}

$pageTitle = $pageTitle ?? 'Command Telemetry';
$flash = Auth::getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — DARK MATTER Telemetry</title>
  <meta name="csrf-token" content="<?= e($csrfToken) ?>">
  <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,800;0,900;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

  <!-- Admin Unified Stylesheet -->
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">

<div class="adm-wrapper">
  <!-- Mobile Sidebar Backdrop Overlay -->
  <div class="adm-overlay" id="admOverlay"></div>

  <!-- Sidebar -->
  <aside class="adm-sidebar" id="admSidebar">
    <a href="index.php" class="adm-sidebar-brand">
      <img src="../assets/images/logo.png" alt="24HRS FITNESS">
      <div>
        <span class="adm-sidebar-brand-title">24HRS FITNESS</span>
        <span class="adm-sidebar-brand-subtitle">ADMIN PORTAL</span>
      </div>
    </a>

    <!-- User Profile Chip -->
    <div class="adm-sidebar-user">
      <div class="adm-avatar">
        <?= strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)) ?>
      </div>
      <div class="adm-user-info">
        <div class="adm-user-name"><?= e($currentUser['username'] ?? 'Admin') ?></div>
        <div class="adm-user-role"><?= e($currentUser['role'] ?? 'Manager') ?></div>
      </div>
    </div>

    <!-- Navigation Links -->
    <nav class="adm-nav">
      <div class="adm-nav-section">Main Menu</div>
      <a href="index.php" class="adm-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>Dashboard</span>
      </a>

      <div class="adm-nav-section">Gym Members & Fees</div>
      <a href="members.php" class="adm-nav-item <?= $currentPage === 'members.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span>Members, Fees & Attendance</span>
      </a>

      <div class="adm-nav-section">Customer Leads</div>
      <a href="enquiries.php" class="adm-nav-item <?= $currentPage === 'enquiries.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        <span>General Inquiries</span>
        <?php if ($pendingEnquiriesCount > 0): ?>
          <span class="adm-nav-badge <?= $pendingEnquiriesCount > 5 ? 'danger' : '' ?>"><?= $pendingEnquiriesCount ?></span>
        <?php endif; ?>
      </a>
      <a href="applications.php" class="adm-nav-item <?= $currentPage === 'applications.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Online Applications</span>
        <?php if ($pendingApplicationsCount > 0): ?>
          <span class="adm-nav-badge <?= $pendingApplicationsCount > 5 ? 'danger' : '' ?>"><?= $pendingApplicationsCount ?></span>
        <?php endif; ?>
      </a>

      <div class="adm-nav-section">Website Content</div>
      <a href="plans.php" class="adm-nav-item <?= $currentPage === 'plans.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
        <span>Membership Plans</span>
      </a>
      <a href="trainers.php" class="adm-nav-item <?= $currentPage === 'trainers.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span>Trainers & Coaches</span>
      </a>
      <a href="programs.php" class="adm-nav-item <?= $currentPage === 'programs.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
        <span>Programs & Facilities</span>
      </a>
      <a href="gallery.php" class="adm-nav-item <?= $currentPage === 'gallery.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
        <span>Photo Gallery</span>
      </a>

      <div class="adm-nav-section">Settings</div>
      <a href="settings.php" class="adm-nav-item <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        <span>Gym & Website Settings</span>
      </a>
    </nav>

    <!-- Sidebar Quick Actions -->
    <div class="adm-sidebar-footer">
      <a href="../index.php" target="_blank" class="adm-btn adm-btn-outline adm-btn-sm" style="width: 100%;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
        <span>View Gym Website</span>
      </a>
      <a href="logout.php" class="adm-btn adm-btn-danger adm-btn-sm" style="width: 100%;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        <span>Log Out</span>
      </a>
    </div>
  </aside>

  <!-- Main Content Area -->
  <div class="adm-main">
    <!-- Topbar -->
    <header class="adm-topbar">
      <div class="adm-topbar-left">
        <button type="button" class="adm-hamburger" id="admHamburger" aria-label="Toggle navigation drawer">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div class="adm-breadcrumb">
          <a href="index.php">Admin</a>
          <span class="sep">/</span>
          <span class="current"><?= e($pageTitle) ?></span>
        </div>
      </div>

      <div class="adm-topbar-right">
        <div class="adm-topbar-time" id="admClock">
          <?= date('Y-m-d H:i') ?>
        </div>
        <a href="logout.php" class="adm-btn adm-btn-outline adm-btn-sm" title="Log Out">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          <span class="d-none d-sm-inline">Log Out</span>
        </a>
      </div>
    </header>

    <!-- Content Area Container -->
    <main class="adm-content">
      <?php if ($flash): ?>
        <div class="adm-alert adm-alert-<?= e($flash['type'] ?? 'info') ?>">
          <div><?= e($flash['message'] ?? '') ?></div>
          <button type="button" class="adm-alert-close" onclick="this.parentElement.remove();">&times;</button>
        </div>
      <?php endif; ?>
