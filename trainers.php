<?php
/**
 * DARK MATTER — Elite Fitness
 * Master Coaches & Scientists (Phase 2 Dynamic PHP/MySQL)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Security.php';

use DarkMatter\Database;

$pageTitle = 'Certified Personal Trainers & Coaches — 24HRS FITNESS';
$pageDesc = 'Meet the certified fitness trainers at 24HRS FITNESS. Specialists in weight loss, strength training, bodybuilding, cardio fitness, and nutrition guidance.';

// Fetch trainers from database
try {
    $trainers = Database::fetchAll("SELECT * FROM `trainers` WHERE `is_active` = 1 ORDER BY `sort_order` ASC");
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS TRAINERS ERROR]: ' . $e->getMessage());
    $trainers = [];
}

require_once __DIR__ . '/includes/header.php';
?>

  <main id="mainContent">

    <!-- ================= SUBPAGE HERO ================= -->
    <section class="subpage-hero">
      <div class="container-custom">
        <span class="mono-tag mb-2">CERTIFIED COACHES</span>
        <h1 class="display-title mb-3">OUR PERSONAL TRAINERS</h1>
        <p class="lead text-silver" style="max-width: 680px;">
          Our team of certified, experienced personal trainers is here to help you achieve your fitness goals with personalized workout routines, form correction, and nutrition advice.
        </p>
      </div>
    </section>

    <!-- ================= TRAINERS ROSTER (MYSQL DYNAMIC) ================= -->
    <section class="section-spacing">
      <div class="container-custom">

        <!-- Trainers Specialty Filter -->
        <div class="filter-tabs-wrapper trainers-filter">
          <button type="button" class="filter-tab active" data-filter="all">ALL COACHES (<?= count($trainers) ?>)</button>
          <button type="button" class="filter-tab" data-filter="strength">STRENGTH & MUSCLE</button>
          <button type="button" class="filter-tab" data-filter="metabolic">FAT LOSS & CARDIO</button>
          <button type="button" class="filter-tab" data-filter="combat">BOXING & CROSSFIT</button>
          <button type="button" class="filter-tab" data-filter="physiology">FITNESS & MOBILITY</button>
        </div>

        <div class="row g-4" id="trainersGrid">
          <?php foreach ($trainers as $coach): ?>
            <?php
              $certs = json_decode($coach['credentials'] ?? '[]', true) ?: [];
              $cat = $coach['category'];
            ?>
            <div class="col-md-6 col-lg-4 trainer-col" data-category="<?= e($cat) ?>">
              <div class="trainer-card">
                <div class="trainer-image">
                  <img src="<?= e($coach['image_url']) ?>" alt="<?= e($coach['name']) ?> - <?= e($coach['role_title']) ?>">
                  <div class="trainer-image-overlay"></div>
                </div>
                <div class="trainer-content">
                  <span class="trainer-role"><?= e($coach['role_title']) ?></span>
                  <h2 class="card-title mb-1"><?= e($coach['name']) ?></h2>
                  <div class="trainer-cert-list">
                    <?php foreach ($certs as $cert): ?>
                      <span class="cert-tag"><?= e($cert) ?></span>
                    <?php endforeach; ?>
                  </div>
                  <p class="text-muted mb-4" style="font-size: 0.88rem;">
                    <?= e($coach['bio']) ?>
                  </p>
                  <button type="button" class="btn-dm btn-lime btn-sm w-100 btn-book-trainer" data-trainer-name="<?= e($coach['name']) ?>" data-trainer-spec="<?= e($coach['specialties']) ?>">
                    <span>BOOK FREE CONSULTATION</span>
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div id="trainersEmptyState" style="display: none; padding: 4rem 1rem; text-align: center;">
          <div class="text-muted mb-2" style="font-family: var(--font-mono);">// NO COACHES FOUND IN THIS CATEGORY</div>
          <p class="text-silver">Please select another specialty filter to view available trainers.</p>
        </div>

      </div>
    </section>

    <!-- ================= COACHING STANDARDS & PHILOSOPHY ================= -->
    <section class="section-spacing" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-metallic);">
      <div class="container-custom">
        <div class="row g-5 align-items-center">
          <div class="col-lg-6">
            <span class="mono-tag mb-2">THE 24HRS FITNESS PROMISE</span>
            <h2 class="section-title mb-4">
              WHY TRAIN WITH <br>
              <span class="text-lime">OUR COACHES?</span>
            </h2>
            <p class="text-silver mb-3">
              Whether you are a beginner stepping into the gym for the first time or looking to break your personal records, our trainers are dedicated to your success:
            </p>
            <div class="d-flex flex-column gap-3 mb-4">
              <div class="p-3" style="background: var(--bg-card); border-left: 3px solid var(--lime-accent);">
                <div class="fw-bold text-white mb-1" style="font-family: var(--font-display); font-size: 1.2rem;">CERTIFIED & EXPERIENCED</div>
                <div class="text-muted" style="font-size: 0.88rem;">All our coaches hold recognized fitness certifications and possess years of hands-on personal training experience.</div>
              </div>
              <div class="p-3" style="background: var(--bg-card); border-left: 3px solid var(--lime-accent);">
                <div class="fw-bold text-white mb-1" style="font-family: var(--font-display); font-size: 1.2rem;">CUSTOM WORKOUT & DIET PLANS</div>
                <div class="text-muted" style="font-size: 0.88rem;">No generic copy-paste workouts. Your coach designs an exercise routine and practical meal guidance tailored to your lifestyle.</div>
              </div>
              <div class="p-3" style="background: var(--bg-card); border-left: 3px solid var(--lime-accent);">
                <div class="fw-bold text-white mb-1" style="font-family: var(--font-display); font-size: 1.2rem;">CORRECT FORM & MOTIVATION</div>
                <div class="text-muted" style="font-size: 0.88rem;">Master proper lifting technique to prevent injury and build muscle safely with continuous guidance and motivation.</div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card-image-wrap" style="aspect-ratio: 4/3; clip-path: var(--clip-chamfer);">
              <img src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=1200&auto=format&fit=crop" alt="Gym weight racks and training floor">
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

<?php
require_once __DIR__ . '/includes/modals.php';
require_once __DIR__ . '/includes/footer.php';
?>
