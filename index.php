<?php
/**
 * DARK MATTER — Elite Fitness
 * Home Page (Phase 2 Dynamic PHP/MySQL)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Security.php';

use DarkMatter\Database;

$pageTitle = '24HRS FITNESS — 24/7 Premium Gym & Training Club';
$pageDesc = '24HRS FITNESS is a modern 24/7 gym with premium strength and cardio equipment, certified personal trainers, steam sauna, and flexible membership plans.';

// Fetch dynamic content from MySQL
try {
    $featuredPrograms = Database::fetchAll("SELECT * FROM `programs` WHERE `is_active` = 1 ORDER BY `sort_order` ASC LIMIT 4");
    $featuredTrainers = Database::fetchAll("SELECT * FROM `trainers` WHERE `is_active` = 1 ORDER BY `sort_order` ASC LIMIT 3");
    $plans = Database::fetchAll("SELECT * FROM `membership_plans` WHERE `is_active` = 1 ORDER BY `sort_order` ASC LIMIT 3");
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS INDEX ERROR]: ' . $e->getMessage());
    $featuredPrograms = [];
    $featuredTrainers = [];
    $plans = [];
}

require_once __DIR__ . '/includes/header.php';
?>

  <!-- Schema.org Structured Data (HealthClub) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "HealthClub",
    "name": "24HRS FITNESS",
    "description": "A premium 24/7 gym equipped with modern strength machines, cardio zones, steam sauna, and certified personal trainers.",
    "url": "<?= e($canonicalUrl) ?>",
    "logo": "<?= e($ogImage) ?>",
    "telephone": "+91-99201-20785",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Plot 42, Main Commercial Avenue, City Center",
      "addressLocality": "Mumbai",
      "addressRegion": "MH",
      "postalCode": "400001",
      "addressCountry": "IN"
    },
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": "19.0760",
      "longitude": "72.8777"
    },
    "openingHoursSpecification": [
      {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
        "opens": "00:00",
        "closes": "23:59"
      }
    ],
    "priceRange": "$$"
  }
  </script>

  <main id="mainContent">

    <!-- ================= HERO SECTION ================= -->
    <section class="hero-section">
      <div class="hero-bg-media">
        <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1920&auto=format&fit=crop" alt="24HRS Fitness modern gym floor with calibrated weights and lighting" fetchpriority="high" loading="eager">
      </div>
      <div class="hero-overlay"></div>

      <div class="container-custom" style="position: relative; z-index: 5;">
        <div class="hero-content">
          <!-- Status Tag -->
          <div class="d-flex align-items-center gap-3 mb-3 reveal-init">
            <div class="status-beacon">
              <span class="beacon-dot"></span>
              <span>OPEN 24 HOURS // 7 DAYS A WEEK</span>
            </div>
          </div>

          <h1 class="display-title mb-3 reveal-init delay-1">
            TRAIN HARDER. <br>
            <span class="text-lime">LIVE BETTER.</span>
          </h1>

          <p class="lead text-silver mb-4 reveal-init delay-2" style="font-size: clamp(1.05rem, 1.8vw, 1.3rem); max-width: 680px; line-height: 1.6;">
            Welcome to 24HRS FITNESS. Over 25,000 square feet of modern gym equipment, certified personal trainers, steam & sauna, and flexible memberships designed to help you reach your goals.
          </p>

          <div class="d-flex flex-wrap gap-3 align-items-center reveal-init delay-3">
            <a href="membership.php" class="btn-dm btn-lime btn-lg">
              <span>VIEW MEMBERSHIP PLANS</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </a>
            <button type="button" class="btn-dm btn-metallic btn-lg" data-open-modal="quickPassModal">
              <span>GET FREE 1-DAY PASS</span>
            </button>
            <a href="contact.php" class="btn-dm btn-outline-lime btn-lg">
              <span>BOOK GYM TOUR</span>
            </a>
          </div>
        </div>

        <!-- Animated Hero Stats Strip -->
        <div class="hero-stats-strip reveal-init delay-4">
          <div class="stat-item">
            <div class="stat-number">
              <span class="counter-val" data-target="25000">0</span>
              <span class="stat-suffix">SQ FT</span>
            </div>
            <div class="stat-label">SPACIOUS GYM FLOOR</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">
              <span class="counter-val" data-target="100">0</span>
              <span class="stat-suffix">+</span>
            </div>
            <div class="stat-label">MODERN FITNESS MACHINES</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">
              <span class="counter-val" data-target="98">0</span>
              <span class="stat-suffix">%</span>
            </div>
            <div class="stat-label">MEMBER SATISFACTION</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">
              <span class="counter-val" data-target="24">0</span>
              <span class="stat-suffix">/ 7</span>
            </div>
            <div class="stat-label">OPEN DAY & NIGHT</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= BRAND MANIFESTO STRIP ================= -->
    <section class="section-spacing bg-angled-top" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-metallic); border-bottom: 1px solid var(--border-metallic);">
      <div class="container-custom">
        <div class="row align-items-center g-5">
          <div class="col-lg-6 reveal-init">
            <span class="mono-tag mb-2">01 // ABOUT OUR GYM</span>
            <h2 class="section-title mb-4">
              BUILT FOR REAL RESULTS.<br>
              <span class="text-lime">POWERED BY PASSION.</span>
            </h2>
            <p class="text-silver mb-3" style="font-size: 1.05rem;">
              At 24HRS FITNESS, we believe a great gym combines top-quality equipment, an energetic atmosphere, and experienced coaches to keep you motivated every single day.
            </p>
            <p class="text-muted mb-4">
              Whether your fitness goal is building muscle, losing fat, increasing stamina, or staying healthy, our clean, spacious facility and certified trainers are here to guide you.
            </p>
            <div class="d-flex flex-wrap gap-4">
              <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lime-accent)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span class="fw-bold" style="font-size: 0.95rem;">Spacious & Clean Floor</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lime-accent)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span class="fw-bold" style="font-size: 0.95rem;">Top Quality Equipment</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lime-accent)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span class="fw-bold" style="font-size: 0.95rem;">Steam & Shower Included</span>
              </div>
            </div>
          </div>
          <div class="col-lg-6 reveal-init delay-2">
            <div class="position-relative">
              <div class="card-image-wrap" style="aspect-ratio: 4/3; clip-path: var(--clip-chamfer);">
                <img src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=1200&auto=format&fit=crop" alt="Gym member training in modern gym lighting">
              </div>
              <!-- Floating Spec Box -->
              <div class="position-absolute bottom-0 start-0 translate-middle-y ms-3 p-3" style="background: rgba(14, 17, 18, 0.92); border: 1px solid var(--lime-border); backdrop-filter: blur(10px); clip-path: var(--clip-chamfer); max-width: 260px;">
                <div class="text-lime fw-bold" style="font-family: var(--font-mono); font-size: 0.75rem;">PROVEN RESULTS</div>
                <div style="font-family: var(--font-display); font-size: 1.3rem; font-weight: 800;">10,000+ SESSIONS</div>
                <div class="text-muted" style="font-size: 0.78rem;">With certified personal trainers</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= FEATURED PROGRAMS (MYSQL DYNAMIC) ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-5">
          <div class="reveal-init">
            <span class="mono-tag mb-2">02 // WORKOUT PROGRAMS</span>
            <h2 class="section-title">POPULAR WORKOUT PROGRAMS</h2>
          </div>
          <div class="reveal-init delay-1 mt-3 mt-md-0">
            <a href="programs.php" class="btn-dm btn-outline-lime">
              <span>VIEW ALL PROGRAMS</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </a>
          </div>
        </div>

        <div class="row g-4">
          <?php foreach ($featuredPrograms as $idx => $prog): ?>
            <?php
              $isHighlight = ($prog['slug'] === 'metabolic-conditioning');
              $delayClass = $idx > 0 ? "delay-{$idx}" : '';
              $intensity = (float) $prog['intensity_score'];
            ?>
            <div class="col-md-6 col-lg-3 reveal-init <?= e($delayClass) ?>">
              <div class="dm-card program-card <?= $isHighlight ? 'dm-card-highlight' : '' ?>">
                <div class="card-image-wrap">
                  <span class="card-badge"><?= e($prog['badge_text']) ?></span>
                  <img src="<?= e($prog['image_url']) ?>" alt="<?= e($prog['name']) ?>">
                </div>
                <h3 class="card-title mb-2"><?= e($prog['name']) ?></h3>
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
                    <span class="metric-label">SESSION</span>
                    <span class="metric-value"><?= (int) $prog['duration_minutes'] ?> MIN</span>
                  </div>
                </div>
                <a href="programs.php" class="btn-dm <?= $isHighlight ? 'btn-lime' : 'btn-metallic' ?> btn-sm w-100 mt-2">
                  <span>VIEW PROGRAM DETAILS</span>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ================= THE GYM ADVANTAGE ================= -->
    <section class="section-spacing" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-metallic); border-bottom: 1px solid var(--border-metallic);">
      <div class="container-custom">
        <div class="text-center max-w-700 mx-auto mb-5 reveal-init" style="max-width: 680px; margin: 0 auto;">
          <span class="mono-tag mb-2">03 // GYM ADVANTAGE</span>
          <h2 class="section-title mb-3">WHY CHOOSE 24HRS FITNESS</h2>
          <p class="text-silver">Modern equipment, certified trainers, and a clean workout environment.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-lg-3 reveal-init">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.4rem;">HEAVY WEIGHTS & MACHINES</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                Dumbbells up to 50kg, Olympic barbells, power racks, cable machines, and cardio treadmills for all fitness levels.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 reveal-init delay-1">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.4rem;">BODY COMPOSITION CHECK</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                Track your body fat percentage, muscle mass gain, and BMI progress with our regular fitness assessments.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 reveal-init delay-2">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.4rem;">SPACIOUS & UNCLUTTERED</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                We maintain ample floor space and plenty of stations so you never have to wait in line during your workouts.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 reveal-init delay-3">
            <div class="dm-card h-100">
              <div class="text-lime mb-3">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
              <h3 class="card-title mb-2" style="font-size: 1.4rem;">24/7 EASY ACCESS</h3>
              <p class="text-muted" style="font-size: 0.9rem;">
                Train early morning, late night, or holidays. Secure entry system gives you total freedom to work out on your schedule.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= TRAINERS PREVIEW (MYSQL DYNAMIC) ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-5">
          <div class="reveal-init">
            <span class="mono-tag mb-2">04 // OUR COACHES</span>
            <h2 class="section-title">EXPERT PERSONAL TRAINERS</h2>
          </div>
          <div class="reveal-init delay-1 mt-3 mt-md-0">
            <a href="trainers.php" class="btn-dm btn-outline-lime">
              <span>MEET ALL TRAINERS</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </a>
          </div>
        </div>

        <div class="row g-4">
          <?php foreach ($featuredTrainers as $idx => $coach): ?>
            <?php
              $delayClass = $idx > 0 ? "delay-{$idx}" : '';
              $certs = json_decode($coach['credentials'] ?? '[]', true) ?: [];
            ?>
            <div class="col-md-4 reveal-init <?= e($delayClass) ?>">
              <div class="trainer-card">
                <div class="trainer-image">
                  <img src="<?= e($coach['image_url']) ?>" alt="<?= e($coach['name']) ?>">
                  <div class="trainer-image-overlay"></div>
                </div>
                <div class="trainer-content">
                  <span class="trainer-role"><?= e($coach['role_title']) ?></span>
                  <h3 class="card-title mb-1"><?= e($coach['name']) ?></h3>
                  <div class="trainer-cert-list">
                    <?php foreach ($certs as $cert): ?>
                      <span class="cert-tag"><?= e($cert) ?></span>
                    <?php endforeach; ?>
                  </div>
                  <p class="text-muted mb-4" style="font-size: 0.88rem;">
                    <?= e($coach['bio']) ?>
                  </p>
                  <button type="button" class="btn-dm btn-metallic btn-sm w-100 btn-book-trainer" data-trainer-name="<?= e($coach['name']) ?>" data-trainer-spec="<?= e($coach['specialties']) ?>">
                    <span>BOOK FREE CONSULTATION</span>
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ================= MEMBERSHIP PREVIEW (MYSQL DYNAMIC) ================= -->
    <section class="section-spacing bg-angled-top" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-metallic);">
      <div class="container-custom">
        <div class="text-center mb-5 reveal-init" style="max-width: 650px; margin: 0 auto;">
          <span class="mono-tag mb-2">05 // MEMBERSHIP PLANS</span>
          <h2 class="section-title mb-3">SIMPLE & AFFORDABLE PLANS</h2>
          <p class="text-silver">Choose the membership plan that fits your workout routine. Cancel anytime with no hidden charges.</p>
        </div>

        <div class="row g-4 justify-content-center">
          <?php foreach ($plans as $idx => $plan): ?>
            <?php
              $isPop = (bool) $plan['is_popular'];
              $delayClass = $idx > 0 ? "delay-{$idx}" : '';
              $features = json_decode($plan['features'] ?? '[]', true) ?: [];
            ?>
            <div class="col-lg-4 col-md-6 reveal-init <?= e($delayClass) ?>">
              <div class="dm-card pricing-card <?= $isPop ? 'dm-card-highlight' : '' ?>">
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="plan-tier-label <?= $isPop ? 'text-lime' : '' ?>"><?= e($plan['tier_label']) ?></span>
                    <?php if ($isPop): ?>
                      <span class="discount-badge">POPULAR</span>
                    <?php endif; ?>
                  </div>
                  <h3 class="card-title mb-1 <?= $isPop ? 'text-white' : '' ?>"><?= e($plan['name']) ?></h3>
                  <p class="text-muted" style="font-size: 0.88rem;"><?= e($plan['tagline']) ?></p>
                  
                  <div class="plan-price-block">
                    <span class="price-currency <?= $isPop ? 'text-lime' : '' ?>">₹</span>
                    <span class="price-amount <?= $isPop ? 'text-lime' : '' ?>"><?= (int) $plan['monthly_price'] ?></span>
                    <span class="price-period">/ month</span>
                    <div class="<?= $isPop ? 'text-lime' : 'text-muted' ?> mt-1" style="font-size: 0.8rem;"><?= e($plan['note_monthly']) ?></div>
                  </div>

                  <div class="feature-list">
                    <?php foreach ($features as $f): ?>
                      <?php
                        $fName = is_array($f) ? (string) ($f['name'] ?? '') : (string) $f;
                        $isIncluded = is_array($f) ? !empty($f['included']) : true;
                        if ($fName === '') continue;
                      ?>
                      <div class="feature-item <?= !$isIncluded ? 'muted' : '' ?>">
                        <?php if ($isIncluded): ?>
                          <svg class="feature-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php else: ?>
                          <svg class="feature-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        <?php endif; ?>
                        <span><?= e($fName) ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <a href="membership.php" class="btn-dm <?= $isPop ? 'btn-lime' : 'btn-metallic' ?> w-100">
                  <span><?= $isPop ? 'JOIN ' . e($plan['name']) : 'VIEW PLAN DETAILS' ?></span>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ================= TESTIMONIALS ================= -->
    <section class="section-spacing">
      <div class="container-custom">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-5">
          <div class="reveal-init">
            <span class="mono-tag mb-2">06 // MEMBER REVIEWS</span>
            <h2 class="section-title">WHAT OUR MEMBERS SAY</h2>
          </div>
          <div class="reveal-init delay-1 text-muted" style="font-family: var(--font-mono); font-size: 0.85rem;">
            RATED 4.9 / 5 BY 500+ ACTIVE MEMBERS
          </div>
        </div>

        <div class="row g-4">
          <div class="col-lg-4 reveal-init">
            <div class="dm-card h-100">
              <div class="d-flex align-items-center gap-1 text-lime mb-3">
                <span>★★★★★</span>
              </div>
              <p class="text-silver mb-4" style="font-size: 0.98rem; font-style: italic;">
                "I have trained at many gyms over the years, but 24HRS FITNESS is hands down the best. Clean equipment, great music, and friendly staff who actually care about your progress."
              </p>
              <div class="d-flex align-items-center gap-3 mt-auto pt-3 border-top" style="border-color: var(--border-metallic) !important;">
                <div style="width: 44px; height: 44px; background: #222; border: 1px solid var(--lime-border); border-radius: 50%; overflow: hidden;">
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150&auto=format&fit=crop" alt="Julian Richter" width="44" height="44" style="object-fit: cover;">
                </div>
                <div>
                  <div class="fw-bold text-white" style="font-family: var(--font-display); font-size: 1.1rem;">JULIAN RICHTER</div>
                  <div class="text-muted" style="font-family: var(--font-mono); font-size: 0.72rem;">MEMBER • GAINED 10KG MUSCLE</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 reveal-init delay-1">
            <div class="dm-card h-100">
              <div class="d-flex align-items-center gap-1 text-lime mb-3">
                <span>★★★★★</span>
              </div>
              <p class="text-silver mb-4" style="font-size: 0.98rem; font-style: italic;">
                "The personal training and steam rooms are fantastic. My trainer created a custom fat-loss plan that helped me drop 12 kg in 4 months without starving."
              </p>
              <div class="d-flex align-items-center gap-3 mt-auto pt-3 border-top" style="border-color: var(--border-metallic) !important;">
                <div style="width: 44px; height: 44px; background: #222; border: 1px solid var(--lime-border); border-radius: 50%; overflow: hidden;">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=150&auto=format&fit=crop" alt="Tara Sterling" width="44" height="44" style="object-fit: cover;">
                </div>
                <div>
                  <div class="fw-bold text-white" style="font-family: var(--font-display); font-size: 1.1rem;">TARA STERLING</div>
                  <div class="text-muted" style="font-family: var(--font-mono); font-size: 0.72rem;">MEMBER • LOST 12 KG</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 reveal-init delay-2">
            <div class="dm-card h-100">
              <div class="d-flex align-items-center gap-1 text-lime mb-3">
                <span>★★★★★</span>
              </div>
              <p class="text-silver mb-4" style="font-size: 0.98rem; font-style: italic;">
                "Being open 24 hours is a lifesaver for my busy schedule. Whether I come at 6 AM or 10 PM, the gym is spotless, well air-conditioned, and ready for a great workout."
              </p>
              <div class="d-flex align-items-center gap-3 mt-auto pt-3 border-top" style="border-color: var(--border-metallic) !important;">
                <div style="width: 44px; height: 44px; background: #222; border: 1px solid var(--lime-border); border-radius: 50%; overflow: hidden;">
                  <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=150&auto=format&fit=crop" alt="Kaelen Mercer" width="44" height="44" style="object-fit: cover;">
                </div>
                <div>
                  <div class="fw-bold text-white" style="font-family: var(--font-display); font-size: 1.1rem;">KAELEN MERCER</div>
                  <div class="text-muted" style="font-family: var(--font-mono); font-size: 0.72rem;">MEMBER • REGULAR 5 DAYS/WEEK</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= CTA BANNER STRIP ================= -->
    <section class="section-spacing" style="background: radial-gradient(circle at 50% 50%, rgba(0, 229, 255, 0.09) 0%, rgba(6, 8, 11, 0.98) 75%); border-top: 1px solid var(--border-metallic);">
      <div class="container-custom text-center">
        <span class="mono-tag mb-3 reveal-init">07 // GET STARTED</span>
        <h2 class="display-title mb-4 reveal-init delay-1">
          READY TO START <br>
          <span class="text-lime">YOUR FITNESS JOURNEY?</span>
        </h2>
        <p class="lead text-silver mb-5 mx-auto reveal-init delay-2" style="max-width: 620px;">
          Visit 24HRS FITNESS today. Get a free 1-day workout trial pass or book a quick gym tour with one of our coaches.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3 reveal-init delay-3">
          <button type="button" class="btn-dm btn-lime btn-lg" data-open-modal="quickPassModal">
            <span>GET FREE 1-DAY PASS</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
          <a href="contact.php" class="btn-dm btn-metallic btn-lg">
            <span>BOOK FREE GYM TOUR</span>
          </a>
          <button type="button" class="btn-dm btn-outline-lime btn-lg btn-whatsapp-cta">
            <span>CHAT ON WHATSAPP</span>
          </button>
        </div>
      </div>
    </section>

  </main>

<?php
require_once __DIR__ . '/includes/modals.php';
require_once __DIR__ . '/includes/footer.php';
?>
