<?php
/**
 * DARK MATTER — Elite Fitness
 * Admin Secure Authentication Portal
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/Database.php';
require_once dirname(__DIR__) . '/includes/Security.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

use DarkMatter\Admin\Auth;
use DarkMatter\Database;
use DarkMatter\Security;

Auth::boot();

// If already authenticated, redirect directly to dashboard
if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$error = null;
$notice = null;

if (isset($_GET['logged_out'])) {
    $notice = 'Security session successfully terminated.';
}

if (isset($_GET['reset_lock']) || isset($_GET['unlock'])) {
    Security::clearRateLimit('admin_auth');
    $notice = 'Security lockout reset successfully. You can now log in.';
}

// Check if any admin exists in the database
$adminCount = 0;
try {
    $res = Database::fetchOne("SELECT COUNT(*) AS cnt FROM `admins`");
    $adminCount = (int) ($res['cnt'] ?? 0);
} catch (\Throwable $e) {
    error_log('[LOGIN DB CHECK ERROR]: ' . $e->getMessage());
}

// Handle POST authentication attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!Security::verifyCsrfToken($submittedToken)) {
        $error = 'Security session expired. Please refresh the portal and retry.';
    } else {
        $identifier = (string) ($_POST['identifier'] ?? '');
        $password   = (string) ($_POST['password'] ?? '');

        if (Auth::attempt($identifier, $password, $authError)) {
            $intended = $_SESSION['dm_admin_intended'] ?? 'index.php';
            unset($_SESSION['dm_admin_intended']);
            header('Location: ' . $intended);
            exit;
        } else {
            $error = $authError ?? 'Authentication verification failed.';
        }
    }
}

$csrfToken = Security::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nexus Authentication — DARK MATTER</title>
  <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/admin.css">

  <style>
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      background: radial-gradient(circle at 50% 20%, rgba(0, 229, 255, 0.08) 0%, #06080B 70%);
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background-color: var(--adm-surface);
      border: 1px solid var(--adm-border);
      padding: 2.25rem 2rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8), 0 0 25px rgba(0, 229, 255, 0.1);
      position: relative;
    }
    .login-card::before {
      content: '';
      position: absolute;
      top: -1px;
      left: -1px;
      width: 40px;
      height: 40px;
      border-top: 2px solid var(--adm-lime);
      border-left: 2px solid var(--adm-lime);
      pointer-events: none;
    }
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    .login-logo {
      height: 76px;
      width: auto;
      object-fit: contain;
      margin-bottom: 1.25rem;
      filter: drop-shadow(0 0 20px rgba(0, 229, 255, 0.55));
    }
    .login-title {
      font-size: 1.6rem;
      letter-spacing: 0.1em;
      margin-bottom: 0.25rem;
    }
    .login-subtitle {
      font-family: var(--adm-font-mono);
      font-size: 0.72rem;
      color: var(--adm-lime);
      letter-spacing: 0.15em;
    }
  </style>
</head>
<body class="admin-body">

<div class="login-container">
  <div class="login-card">
    <div class="login-header">
      <img src="../assets/images/logo.png" alt="24HRS FITNESS" class="login-logo">
      <h1 class="login-title">ADMIN LOGIN</h1>
      <div class="login-subtitle">Sign in to manage 24HRS FITNESS</div>
    </div>

    <?php if ($adminCount === 0): ?>
      <div class="adm-alert adm-alert-warning" style="font-size: 0.8rem;">
        <div>
          <strong>Initial Setup:</strong> No admin account found. Run CLI command:
          <pre style="margin: 0.5rem 0 0 0; background: #000; padding: 0.5rem; color: var(--adm-lime); font-size: 0.75rem;">php scripts/create_admin.php</pre>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($notice): ?>
      <div class="adm-alert adm-alert-success" style="font-size: 0.82rem;">
        <div><?= e($notice) ?></div>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="adm-alert adm-alert-danger" style="font-size: 0.82rem;">
        <div>
          <?= e($error) ?>
          <?php if (str_contains($error, 'locked')): ?>
            <div style="margin-top: 8px;">
              <a href="login.php?reset_lock=1" style="color: var(--adm-lime); text-decoration: underline; font-weight: 700;">
                &rarr; [Click Here to Unlock Immediately]
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

      <div style="margin-bottom: 1.25rem;">
        <label class="adm-label" for="identifier">Username or Email Address</label>
        <input 
          type="text" 
          id="identifier" 
          name="identifier" 
          class="adm-input" 
          required 
          autofocus 
          placeholder="fitness_gym@gmail.com"
          value="<?= e($_POST['identifier'] ?? '') ?>"
        >
      </div>

      <div style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
          <label class="adm-label" for="password" style="margin-bottom: 0;">Password</label>
        </div>
        <input 
          type="password" 
          id="password" 
          name="password" 
          class="adm-input" 
          required 
          placeholder="Enter your password"
        >
      </div>

      <button type="submit" class="adm-btn adm-btn-lime" style="width: 100%; margin-bottom: 1.25rem;">
        Sign In to Dashboard &rarr;
      </button>

      <div style="text-align: center; font-size: 0.75rem;">
        <a href="../index.php" style="color: var(--adm-silver-dim); text-decoration: none;">
          &larr; Back to Gym Website
        </a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
