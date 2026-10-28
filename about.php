<?php
/**
 * DARK MATTER — Elite Fitness
 * About & Spatial Gallery (Phase 2 Dynamic PHP/MySQL)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Security.php';

use DarkMatter\Database;

$pageTitle = 'About Us & Photo Gallery — 24HRS FITNESS';
$pageDesc = 'Learn about 24HRS FITNESS, our gym facilities, modern workout equipment, and explore our photo gallery.';

// Fetch gallery images from database
try {
    $galleryImages = Database::fetchAll("SELECT * FROM `gallery_images` WHERE `is_active` = 1 ORDER BY `sort_order` ASC");
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS ABOUT GALLERY ERROR]: ' . $e->getMessage());
    $galleryImages = [];
}

$extraScripts = ['js/gallery.js'];
require_once __DIR__ . '/includes/header.php';
?>

  <main id="mainContent">

    <!-- ================= SUBPAGE HERO ================= -->
    <section class="subpage-hero">
      <div class="container-custom">
        <span class="mono-tag mb-2">ABOUT OUR GYM</span>
        <h1 class="display-title mb-3">OUR STORY & GYM GALLERY</h1>
        <p class="lead text-silver" style="max-width: 680px;">
          24HRS FITNESS is a modern 24/7 fitness club built for people who want top-quality gym equipment, clean facilities, and dedicated personal coaches.
        </p>
      </div>
    </section>

    <!-- ================= THE STORY SECTION ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        <div class="row g-5 align-items-center">
          <div class="col-lg-6">
            <span class="mono-tag mb-2">01 // OUR MISSION</span>
            <h2 class="section-title mb-4">
              COMMITTED TO YOUR <br>
              <span class="text-lime">FITNESS & HEALTH.</span>
            </h2>
            <p class="text-silver mb-3" style="font-size: 1.05rem;">
              Founded with the vision to create an inspiring fitness destination, 24HRS FITNESS brings together heavy iron lifting, cardio endurance, steam sauna, and certified coaching under one roof.
            </p>
            <p class="text-muted mb-4">
              We believe everyone deserves a motivating place to train—whether you are working out for the first time or training for peak athletic performance. With clean spaces, plenty of equipment, and 24/7 access, your fitness goals are always within reach.
            </p>
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="p-3" style="background: var(--bg-card); border: 1px solid var(--border-metallic);">
                  <div class="text-lime fw-bold" style="font-family: var(--font-display); font-size: 1.5rem;">TOP QUALITY</div>
                  <div class="text-muted" style="font-size: 0.82rem;">Modern weights & commercial machines.</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-3" style="background: var(--bg-card); border: 1px solid var(--border-metallic);">
                  <div class="text-lime fw-bold" style="font-family: var(--font-display); font-size: 1.5rem;">OPEN 24/7</div>
                  <div class="text-muted" style="font-size: 0.82rem;">Train day or night at your convenience.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card-image-wrap" style="aspect-ratio: 4/3; clip-path: var(--clip-chamfer);">
              <img src="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?q=80&w=1200&auto=format&fit=crop" alt="24HRS Fitness Gym Floor and Weight Training Stations">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= 4 CORE BRAND PILLARS ================= -->
    <section class="section-spacing bg-angled-top" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-metallic); border-bottom: 1px solid var(--border-metallic);">
      <div class="container-custom">
        <div class="text-center mb-5" style="max-width: 650px; margin: 0 auto;">
          <span class="mono-tag mb-2">02 // OUR VALUES</span>
          <h2 class="section-title mb-3">WHY TRAIN AT 24HRS FITNESS</h2>
          <p class="text-silver">The 4 foundational pillars behind our premium gym experience.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">TOP QUALITY GEAR</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                Commercial power racks, Olympic barbells, dumbbells up to 50kg, and smooth dual cable stations.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">BODY PROGRESS CHECKS</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                Regular body composition analysis, BMI assessments, and trainer guidance to keep your goals on track.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="m4.93 4.93 4.24 4.24"></path><path d="m14.83 9.17 4.24-4.24"></path><path d="m14.83 14.83 4.24 4.24"></path><path d="m9.17 14.83-4.24 4.24"></path></svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">STEAM & SAUNA</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                Relax after your workout with hygienic steam rooms, Finnish saunas, and hot showers for complete recovery.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">CLEAN & FRIENDLY</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                A welcoming, supportive fitness community where members train with focus, respect, and zero distractions.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= INTERACTIVE FACILITY GALLERY (MYSQL DYNAMIC) ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
          <div>
            <span class="mono-tag mb-2">03 // PHOTO GALLERY</span>
            <h2 class="section-title">OUR GYM PHOTO GALLERY</h2>
          </div>
          <div class="text-muted mt-2 mt-md-0" style="font-family: var(--font-mono); font-size: 0.85rem;">
            CLICK ANY PHOTO TO VIEW FULL SCREEN
          </div>
        </div>

        <!-- Gallery Filter Tabs -->
        <div class="filter-tabs-wrapper gallery-filter">
          <button type="button" class="filter-tab active" data-filter="all">ALL PHOTOS (<?= count($galleryImages) ?>)</button>
          <button type="button" class="filter-tab" data-filter="iron">WEIGHT TRAINING</button>
          <button type="button" class="filter-tab" data-filter="spa">STEAM & SAUNA</button>
          <button type="button" class="filter-tab" data-filter="turf">CROSSFIT TURF</button>
          <button type="button" class="filter-tab" data-filter="suites">CARDIO ZONE</button>
        </div>

        <!-- Gallery Grid -->
        <div class="gallery-grid" id="galleryGrid">
          <?php foreach ($galleryImages as $item): ?>
            <div class="gallery-item" data-category="<?= e($item['category']) ?>">
              <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['alt_text']) ?>">
              <div class="gallery-overlay">
                <span class="gallery-tag"><?= e($item['tag_label']) ?></span>
                <div class="gallery-caption"><?= e($item['title']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div id="galleryEmptyState" style="display: none; padding: 4rem 1rem; text-align: center;">
          <div class="text-muted mb-2" style="font-family: var(--font-mono);">// NO PHOTOS IN THIS CATEGORY</div>
          <p class="text-silver">Please select another category tab to view gym photos.</p>
        </div>

      </div>
    </section>

  </main>

  <!-- ================= LIGHTBOX MODAL ================= -->
  <div id="galleryLightbox" class="lightbox-modal" aria-hidden="true" role="dialog" aria-labelledby="lightboxTitle">
    <button id="lightboxClose" class="lightbox-close" aria-label="Close viewer">✕</button>
    <button id="lightboxPrev" class="lightbox-prev" aria-label="Previous image">‹</button>
    <button id="lightboxNext" class="lightbox-next" aria-label="Next image">›</button>

    <div class="lightbox-content">
      <img id="lightboxImg" src="" alt="Dark Matter Facility Space">
      <div class="lightbox-info">
        <div id="lightboxTag" class="mono-tag mb-1">ARENA 01</div>
        <div id="lightboxTitle" class="lightbox-title">Facility View</div>
      </div>
    </div>
  </div>

<?php
require_once __DIR__ . '/includes/modals.php';
require_once __DIR__ . '/includes/footer.php';
?>
