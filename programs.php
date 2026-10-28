<?php
/**
 * DARK MATTER — Elite Fitness
 * Programs & Facilities (Phase 2 Dynamic PHP/MySQL)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Security.php';

use DarkMatter\Database;

$pageTitle = 'Workout Programs & Gym Facilities — 24HRS FITNESS';
$pageDesc = 'Explore workout programs and gym facilities at 24HRS FITNESS. Weight training, cardio, crossfit, steam sauna, and certified personal trainers.';

// Fetch programs from database
try {
    $programs = Database::fetchAll("SELECT * FROM `programs` WHERE `is_active` = 1 ORDER BY `sort_order` ASC");
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS PROGRAMS ERROR]: ' . $e->getMessage());
    $programs = [];
}

// Build protocol details dictionary for modal inspection
$protocolDetails = [];
foreach ($programs as $prog) {
    $protocolDetails[$prog['slug']] = [
        'title'               => $prog['name'],
        'category'            => $prog['badge_text'],
        'intensity'           => $prog['intensity_display'],
        'duration'            => $prog['duration_minutes'] . ' Minutes',
        'caloricExpenditure'  => $prog['burn_calories_range'],
        'targetAdaptation'    => $prog['target_adaptation'],
        'equipmentStations'   => $prog['gear_stations'],
        'coachRatio'          => $prog['coach_ratio'],
        'recommendedCadence'  => $prog['recommended_cadence']
    ];
}

$extraScripts = ['js/programs.js'];
require_once __DIR__ . '/includes/header.php';
?>

  <main id="mainContent">

    <!-- ================= SUBPAGE HERO ================= -->
    <section class="subpage-hero">
      <div class="container-custom">
        <span class="mono-tag mb-2">WORKOUT BLUEPRINTS</span>
        <h1 class="display-title mb-3">WORKOUT PROGRAMS & GYM ZONES</h1>
        <p class="lead text-silver" style="max-width: 680px;">
          Every program is designed by certified fitness coaches to help you burn fat, build lean muscle, and stay energetic. Explore our programs below.
        </p>
      </div>
    </section>

    <!-- ================= FILTERABLE PROGRAMS SECTION (MYSQL DYNAMIC) ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        
        <!-- Filter Tabs -->
        <div class="filter-tabs-wrapper programs-filter">
          <button type="button" class="filter-tab active" data-filter="all">ALL PROGRAMS (<?= count($programs) ?>)</button>
          <button type="button" class="filter-tab" data-filter="strength">STRENGTH & MUSCLE</button>
          <button type="button" class="filter-tab" data-filter="cardio">CARDIO & FAT LOSS</button>
          <button type="button" class="filter-tab" data-filter="recovery">RECOVERY & WELLNESS</button>
          <button type="button" class="filter-tab" data-filter="functional">CROSSFIT & BOXING</button>
        </div>

        <!-- Programs Grid -->
        <div class="row g-4" id="programsGrid">
          
          <?php foreach ($programs as $prog): ?>
            <?php
              $intensity = (float) $prog['intensity_score'];
              $cat = $prog['category'];
            ?>
            <div class="col-md-6 col-lg-4 program-grid-item" data-category="<?= e($cat) ?>">
              <div class="dm-card program-card">
                <div class="card-image-wrap">
                  <span class="card-badge"><?= e($prog['badge_text']) ?></span>
                  <img src="<?= e($prog['image_url']) ?>" alt="<?= e($prog['name']) ?>">
                </div>
                <h2 class="card-title mb-2"><?= e($prog['name']) ?></h2>
                <p class="text-muted mb-auto" style="font-size: 0.9rem;">
                  <?= e($prog['short_desc']) ?>
                </p>
                <div class="program-metrics">
                  <div class="metric-item">
                    <span class="metric-label">INTENSITY</span>
                    <div class="intensity-dots">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="intensity-dot <?= $i <= $intensity ? 'filled' : '' ?>"></span>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <div class="metric-item">
                    <span class="metric-label">CALORIE BURN</span>
                    <span class="metric-value"><?= e($prog['burn_calories_range']) ?></span>
                  </div>
                  <div class="metric-item">
                    <span class="metric-label">DURATION</span>
                    <span class="metric-value"><?= (int) $prog['duration_minutes'] ?> MIN</span>
                  </div>
                  <div class="metric-item">
                    <span class="metric-label">TRAINER RATIO</span>
                    <span class="metric-value"><?= e($prog['coach_ratio']) ?></span>
                  </div>
                </div>
                <button type="button" class="btn-dm btn-lime btn-sm w-100 btn-inspect-protocol" data-protocol-id="<?= e($prog['slug']) ?>">
                  <span>VIEW PROGRAM DETAILS</span>
                </button>
              </div>
            </div>
          <?php endforeach; ?>

        </div>

        <!-- Empty Filter State Container -->
        <div id="programsEmptyState" style="display: none; padding: 4rem 1rem; text-align: center;">
          <div class="text-muted mb-3" style="font-family: var(--font-mono);">// NO PROGRAMS FOUND</div>
          <p class="text-silver">Please select another category tab to view active workout programs.</p>
        </div>

      </div>
    </section>

    <!-- ================= FACILITY GYM ZONES ================= -->
    <section class="section-spacing" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-metallic);">
      <div class="container-custom">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-5">
          <div>
            <span class="mono-tag mb-2">FACILITY ZONES</span>
            <h2 class="section-title">OUR 4 GYM ZONES</h2>
          </div>
          <div class="mt-3 mt-md-0">
            <a href="about.php" class="btn-dm btn-metallic">
              <span>VIEW ABOUT & GALLERY</span>
            </a>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="card-image-wrap mb-3" style="height: 180px;">
                <img src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=600&auto=format&fit=crop" alt="Strength and Weight Training Zone">
              </div>
              <span class="mono-tag mb-1" style="font-size: 0.7rem;">ZONE 01 // 10,000 SQ FT</span>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">STRENGTH & FREE WEIGHTS</h3>
              <p class="text-muted" style="font-size: 0.88rem;">
                Heavy dumbbells up to 50kg, Olympic power racks, Smith machines, flat & incline benches, and dual cable towers.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="card-image-wrap mb-3" style="height: 180px;">
                <img src="https://images.unsplash.com/photo-1538805060514-97d9cc17730c?q=80&w=600&auto=format&fit=crop" alt="Cardio & Endurance Area">
              </div>
              <span class="mono-tag mb-1" style="font-size: 0.7rem;">ZONE 02 // 6,000 SQ FT</span>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">CARDIO & ENDURANCE ZONE</h3>
              <p class="text-muted" style="font-size: 0.88rem;">
                Modern commercial treadmills, elliptical trainers, spin bikes, stair climbers, and rowing machines with live metrics.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="card-image-wrap mb-3" style="height: 180px;">
                <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=600&auto=format&fit=crop" alt="Steam, Sauna and Recovery">
              </div>
              <span class="mono-tag mb-1" style="font-size: 0.7rem;">ZONE 03 // 4,000 SQ FT</span>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">STEAM & SAUNA RECOVERY</h3>
              <p class="text-muted" style="font-size: 0.88rem;">
                Relax after your workout with our hygienic hot steam rooms, dry Finnish cedar saunas, and hot shower facilities.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="dm-card h-100">
              <div class="card-image-wrap mb-3" style="height: 180px;">
                <img src="https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?q=80&w=600&auto=format&fit=crop" alt="Crossfit and Functional Turf">
              </div>
              <span class="mono-tag mb-1" style="font-size: 0.7rem;">ZONE 04 // 5,000 SQ FT</span>
              <h3 class="card-title mb-2" style="font-size: 1.35rem;">CROSSFIT & SPRINT TURF</h3>
              <p class="text-muted" style="font-size: 0.88rem;">
                Sprint turf, heavy sled pushes, battle ropes, plyometric jump boxes, kettlebells, and suspension training zone.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= PROTOCOL TIMETABLE MATRIX ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        <div class="text-center mb-5" style="max-width: 650px; margin: 0 auto;">
          <span class="mono-tag mb-2">WEEKLY CLASS TIMETABLE</span>
          <h2 class="section-title mb-3">GROUP WORKOUT SCHEDULE</h2>
          <p class="text-silver">The gym floor is open 24/7 for regular workouts. Guided group sessions operate on the following schedule:</p>
        </div>

        <div class="comparison-table-wrapper">
          <table class="comparison-table">
            <thead>
              <tr>
                <th scope="col">TIME WINDOW</th>
                <th scope="col">MON / WED / FRI</th>
                <th scope="col">TUE / THU</th>
                <th scope="col">SATURDAY</th>
                <th scope="col">SUNDAY</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>06:30 – 07:45</strong></td>
                <td><span class="text-lime fw-bold">Morning Muscle Build</span></td>
                <td>Fat Burn HIIT</td>
                <td>Open Weight Floor</td>
                <td>Steam & Stretch</td>
              </tr>
              <tr>
                <td><strong>08:30 – 09:30</strong></td>
                <td>Fat Burn HIIT</td>
                <td>Boxing & Kickboxing</td>
                <td>Endurance Run</td>
                <td>Yoga & Mobility</td>
              </tr>
              <tr>
                <td><strong>12:00 – 13:00</strong></td>
                <td>Lunch Break Strength</td>
                <td>Express Cardio Blast</td>
                <td>Trainer Workshop</td>
                <td>Open Floor</td>
              </tr>
              <tr>
                <td><strong>17:30 – 18:45</strong></td>
                <td><span class="text-lime fw-bold">Evening Strength Club</span></td>
                <td>Crossfit Circuit</td>
                <td>Open Floor</td>
                <td>Steam & Sauna</td>
              </tr>
              <tr>
                <td><strong>19:00 – 20:00</strong></td>
                <td>Boxing Drills</td>
                <td>Calisthenics & Core</td>
                <td>Group Fitness</td>
                <td>Relaxation & Sauna</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

  </main>

  <!-- Dynamic Protocol Data from MySQL injected for JS protocol inspector modal -->
  <script id="serverProtocolData" type="application/json">
    <?= json_encode($protocolDetails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
  </script>

<?php
require_once __DIR__ . '/includes/modals.php';
require_once __DIR__ . '/includes/footer.php';
?>
