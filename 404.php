<?php
/**
 * DARK MATTER — Elite Fitness
 * Custom 404 Not Found Experience
 */

declare(strict_types=1);

http_response_code(404);

$pageTitle = '404 // Signal Lost — DARK MATTER';
$pageDesc  = 'The requested facility coordinates or training protocol could not be resolved in the Dark Matter nexus.';

require_once __DIR__ . '/includes/header.php';
?>

<main id="mainContent" style="min-height: 75vh; display: flex; align-items: center; justify-content: center; padding: 4rem 1.5rem; text-align: center;">
  <div style="max-width: 600px; margin: 0 auto;">
    <div style="font-family: var(--font-mono, 'Space Mono', monospace); font-size: 0.85rem; color: var(--dm-lime, #00E5FF); letter-spacing: 0.2em; margin-bottom: 1rem; text-transform: uppercase;">
      // ERROR CODE 404 //
    </div>
    
    <h1 style="font-family: var(--font-heading, 'Barlow Condensed', sans-serif); font-size: clamp(3rem, 8vw, 6rem); font-weight: 900; line-height: 0.95; letter-spacing: 0.05em; color: var(--dm-white, #F5F5F5); margin-bottom: 1.5rem; text-transform: uppercase;">
      SIGNAL LOST IN THE VOID
    </h1>

    <p style="font-family: var(--font-body, 'Plus Jakarta Sans', sans-serif); font-size: 1.05rem; color: var(--dm-silver, #BFC3C7); line-height: 1.6; margin-bottom: 2.5rem;">
      The requested facility sector, protocol document, or athlete telemetry transmission does not exist or has been relocated to classified coordinates.
    </p>

    <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
      <a href="index.php" class="btn-dm btn-lime">
        <span>RETURN TO BASE</span>
      </a>
      <a href="programs.php" class="btn-dm btn-outline">
        <span>INSPECT PROTOCOLS</span>
      </a>
      <a href="contact.php" class="btn-dm btn-outline">
        <span>CONTACT CONCIERGE</span>
      </a>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
