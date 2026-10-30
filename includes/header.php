<?php
/**
 * DARK MATTER — Elite Fitness
 * Shared Header Partial (PHP Component)
 */

declare(strict_types=1);

require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/Database.php';

use DarkMatter\Security;
use DarkMatter\Database;
use DarkMatter\Env;

Security::initSession();
$csrfToken = Security::getCsrfToken();

// Active page detection
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
if ($currentPage === '') {
    $currentPage = 'index.php';
}

$pageTitle = $pageTitle ?? '24HRS FITNESS — 24/7 Premium Gym & Training Club';
$pageDesc = $pageDesc ?? '24HRS FITNESS is a premium 24/7 gym with modern workout equipment, certified personal trainers, steam & sauna, and affordable membership plans.';

$appUrl = rtrim((string) Env::get('APP_URL', 'https://darkmatter-fitness.com'), '/');
$canonicalUrl = ($currentPage === 'index.php') ? $appUrl . '/' : $appUrl . '/' . $currentPage;
$ogImage = $ogImage ?? ($appUrl . '/assets/images/logo.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta name="csrf-token" content="<?= e($csrfToken) ?>">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">

  <!-- Open Graph / Facebook / LinkedIn -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="24HRS FITNESS">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <meta property="og:image" content="<?= e($ogImage) ?>">

  <!-- Twitter / X -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($pageTitle) ?>">
  <meta name="twitter:description" content="<?= e($pageDesc) ?>">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">

  <!-- Bootstrap 5 Grid & Utilities -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap-grid.min.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/components.css">
</head>

<body>

  <!-- Accessible Skip to Main Content Link -->
  <a href="#mainContent" class="skip-link">Skip to main content</a>

  <!-- ================= SITE HEADER / NAVIGATION ================= -->
  <header class="site-header">
    <div class="container-custom nav-container">
      <a href="index.php" class="brand-logo" aria-label="24HRS Fitness Home">
        <img src="assets/images/logo.png" alt="24HRS Fitness Logo" width="190" height="58">
      </a>

      <!-- Desktop Nav Menu -->
      <nav class="nav-menu" aria-label="Primary Navigation">
        <a href="index.php" class="nav-link <?= in_array($currentPage, ['index.php', 'index.html', '']) ? 'active' : '' ?>">Home</a>
        <a href="programs.php" class="nav-link <?= $currentPage === 'programs.php' ? 'active' : '' ?>">Workout Programs</a>
        <a href="membership.php" class="nav-link <?= $currentPage === 'membership.php' ? 'active' : '' ?>">Membership Plans</a>
        <a href="trainers.php" class="nav-link <?= $currentPage === 'trainers.php' ? 'active' : '' ?>">Trainers</a>
        <a href="about.php" class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>">About & Gallery</a>
        <a href="contact.php" class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>">Contact Us</a>
      </nav>

      <!-- Nav CTA & Mobile Hamburger -->
      <div class="nav-actions">
        <a href="contact.php" class="btn-dm btn-outline-lime btn-sm">
          <span>Book Free Tour</span>
        </a>
        <button type="button" class="btn-dm btn-lime btn-sm" data-open-modal="quickPassModal">
          <span>Get Free Pass</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>

        <!-- Mobile Menu Toggle Button -->
        <button class="menu-toggle" aria-label="Toggle navigation drawer" aria-expanded="false" aria-controls="mobileDrawer">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile Drawer Overlay & Panel -->
  <div class="mobile-drawer-overlay" aria-hidden="true"></div>
  <aside id="mobileDrawer" class="mobile-drawer" aria-label="Mobile Navigation Drawer">
    <div>
      <div class="d-flex align-items-center justify-content-between mb-4">
        <img src="assets/images/logo.png" alt="24HRS Fitness" width="180" style="height: 52px; width: auto; object-fit: contain; filter: drop-shadow(0 0 16px rgba(0, 229, 255, 0.5));">
        <div class="status-beacon">
          <span class="beacon-dot"></span>
          <span>OPEN 24/7</span>
        </div>
      </div>

      <nav class="mobile-nav-links">
        <a href="index.php" class="nav-link <?= in_array($currentPage, ['index.php', 'index.html', '']) ? 'active' : '' ?>">Home</a>
        <a href="programs.php" class="nav-link <?= $currentPage === 'programs.php' ? 'active' : '' ?>">Workout Programs</a>
        <a href="membership.php" class="nav-link <?= $currentPage === 'membership.php' ? 'active' : '' ?>">Membership Plans</a>
        <a href="trainers.php" class="nav-link <?= $currentPage === 'trainers.php' ? 'active' : '' ?>">Trainers & Coaches</a>
        <a href="about.php" class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>">About & Gallery</a>
        <a href="contact.php" class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>">Contact Us</a>
      </nav>
    </div>

    <div class="mobile-drawer-footer">
      <button type="button" class="btn-dm btn-lime w-100" data-open-modal="quickPassModal">
        <span>Get Free 1-Day Trial Pass</span>
      </button>
      <a href="contact.php" class="btn-dm btn-metallic w-100">
        <span>Book Gym Tour</span>
      </a>
      <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.78rem; font-family: var(--font-mono);">
        <span class="text-muted">OPEN 24 HOURS</span>
        <a href="admin/login.php" style="color: var(--dm-silver); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
          <span style="color: var(--dm-lime);">&bull;</span> Staff / Admin Login
        </a>
      </div>
    </div>
  </aside>
