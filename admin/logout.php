<?php
/**
 * DARK MATTER — Elite Fitness
 * Admin Secure Logout Handler
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

use DarkMatter\Admin\Auth;

Auth::logout();
header('Location: login.php?logged_out=1');
exit;
