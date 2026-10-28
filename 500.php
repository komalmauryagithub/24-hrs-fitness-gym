<?php
/**
 * DARK MATTER — Elite Fitness
 * Custom 500 Internal Error Experience
 */

declare(strict_types=1);

http_response_code(500);

$pageTitle = '500 // Telemetry Interrupted — DARK MATTER';
$pageDesc  = 'Internal facility telemetry anomaly detected. Engineering team alerted.';

// Safe inclusion without throwing recursive DB errors
require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 // TELEMETRY ANOMALY — DARK MATTER</title>
  <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/components.css">
</head>
<body style="background-color: #080A0A; color: #F5F5F5; font-family: 'Plus Jakarta Sans', sans-serif;">

<main style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 3rem 1.5rem; text-align: center;">
  <div style="max-width: 600px; margin: 0 auto;">
    <div style="font-family: 'Space Mono', monospace; font-size: 0.85rem; color: #FF4757; letter-spacing: 0.2em; margin-bottom: 1rem; text-transform: uppercase;">
      // ERROR CODE 500 //
    </div>
    
    <h1 style="font-family: 'Barlow Condensed', sans-serif; font-size: clamp(2.5rem, 7vw, 5rem); font-weight: 900; line-height: 0.95; letter-spacing: 0.05em; color: #F5F5F5; margin-bottom: 1.5rem; text-transform: uppercase;">
      TELEMETRY INTERRUPTED
    </h1>

    <p style="font-size: 1.05rem; color: #BFC3C7; line-height: 1.6; margin-bottom: 2.5rem;">
      A momentary telemetry interruption occurred within our facility server architecture. Engineering diagnostics have been securely logged. No personal data was compromised.
    </p>

    <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
      <a href="index.php" class="btn-dm btn-lime">
        <span>RETURN TO BASE</span>
      </a>
      <a href="contact.php" class="btn-dm btn-outline">
        <span>CONCIERGE HOTLINE</span>
      </a>
    </div>
  </div>
</main>

</body>
</html>
