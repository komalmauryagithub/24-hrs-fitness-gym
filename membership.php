<?php
/**
 * DARK MATTER — Elite Fitness
 * Membership Plans (Phase 2 Dynamic PHP/MySQL)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Security.php';

use DarkMatter\Database;

$pageTitle = 'Membership Plans & Pricing — 24HRS FITNESS';
$pageDesc = 'Affordable and flexible gym membership plans at 24HRS FITNESS. Monthly and annual options with 24/7 gym access, personal training, steam and sauna.';

// Fetch plans from database
try {
    $plans = Database::fetchAll("SELECT * FROM `membership_plans` WHERE `is_active` = 1 ORDER BY `sort_order` ASC");
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS MEMBERSHIP ERROR]: ' . $e->getMessage());
    $plans = [];
}

// Build JSON payload for client-side toggle calculations
$pricingData = [
    'monthly' => [],
    'annual'  => []
];

foreach ($plans as $p) {
    $key = $p['slug'];
    $pricingData['monthly'][$key] = [
        'id'     => (int) $p['id'],
        'name'   => $p['name'],
        'price'  => (float) $p['monthly_price'],
        'period' => '/ month',
        'note'   => $p['note_monthly']
    ];
    $pricingData['annual'][$key] = [
        'id'     => (int) $p['id'],
        'name'   => $p['name'],
        'price'  => (float) $p['annual_monthly_price'],
        'period' => '/ month',
        'note'   => $p['note_annual']
    ];
}

$extraScripts = ['js/membership.js'];
require_once __DIR__ . '/includes/header.php';
?>

  <main id="mainContent">

    <!-- ================= SUBPAGE HERO ================= -->
    <section class="subpage-hero">
      <div class="container-custom">
        <span class="mono-tag mb-2">PRICING & MEMBERSHIPS</span>
        <h1 class="display-title mb-3">MEMBERSHIP PLANS</h1>
        <p class="lead text-silver" style="max-width: 680px;">
          No hidden charges. No pushy sales tactics. Choose a flexible monthly plan or save more with an annual membership. Join today and start training.
        </p>
      </div>
    </section>

    <!-- ================= BILLING TOGGLE & PRICING CARDS (MYSQL DYNAMIC) ================= -->
    <section class="section-spacing">
      <div class="container-custom">

        <!-- Billing Toggle Component -->
        <div class="billing-toggle-container">
          <span id="labelMonthly" class="billing-label active">MONTHLY BILLING</span>
          <div id="billingSwitch" class="switch-pill" role="switch" aria-checked="false" aria-label="Toggle annual billing discount">
            <div class="switch-knob"></div>
          </div>
          <span id="labelAnnual" class="billing-label">ANNUAL BILLING</span>
          <span class="discount-badge">SAVE 20%</span>
        </div>

        <!-- 3 Tier Cards Row -->
        <div class="row g-4 justify-content-center">

          <?php foreach ($plans as $idx => $plan): ?>
            <?php
              $isPop = (bool) $plan['is_popular'];
              $features = json_decode($plan['features'] ?? '[]', true) ?: [];
              $slug = $plan['slug'];
            ?>
            <div class="col-lg-4 col-md-6">
              <div class="dm-card pricing-card <?= $isPop ? 'dm-card-highlight' : '' ?>">
                <div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="plan-tier-label <?= $isPop ? 'text-lime' : '' ?>"><?= e($plan['tier_label']) ?></span>
                    <?php if ($isPop): ?>
                      <span class="discount-badge">POPULAR</span>
                    <?php endif; ?>
                  </div>
                  <h2 class="card-title mb-1 <?= $isPop ? 'text-white' : '' ?>"><?= e($plan['name']) ?></h2>
                  <p class="text-muted" style="font-size: 0.88rem;"><?= e($plan['tagline']) ?></p>
                  
                  <div class="plan-price-block">
                    <span class="price-currency <?= $isPop ? 'text-lime' : '' ?>">₹</span>
                    <span id="price-<?= e($slug) ?>" class="price-amount <?= $isPop ? 'text-lime' : '' ?>"><?= (int) $plan['monthly_price'] ?></span>
                    <span class="price-period">/ month</span>
                    <div id="note-<?= e($slug) ?>" class="<?= $isPop ? 'text-lime' : 'text-muted' ?> mt-2" style="font-size: 0.8rem;"><?= e($plan['note_monthly']) ?></div>
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

                <button type="button" class="btn-dm <?= $isPop ? 'btn-lime' : 'btn-metallic' ?> w-100 btn-select-plan" data-plan="<?= e($slug) ?>" data-plan-name="<?= e($plan['name']) ?>" data-plan-id="<?= (int) $plan['id'] ?>">
                  <span>JOIN <?= e($plan['name']) ?></span>
                </button>
              </div>
            </div>
          <?php endforeach; ?>

        </div>

        <!-- ================= FULL FEATURE COMPARISON MATRIX ================= -->
        <div class="comparison-table-wrapper">
          <table class="comparison-table">
            <thead>
              <tr>
                <th scope="col">FEATURE COMPARISON</th>
                <th scope="col">STARTER (₹1,500/mo)</th>
                <th scope="col" style="color: var(--lime-accent);">PRO FITNESS (₹2,500/mo)</th>
                <th scope="col">ELITE VIP (₹4,500/mo)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>24/7 Gym Floor Access</td>
                <td><span class="table-check">✓</span></td>
                <td><span class="table-check">✓</span></td>
                <td><span class="table-check">✓</span></td>
              </tr>
              <tr>
                <td>Heavy Iron & Cardio Equipment</td>
                <td><span class="table-check">✓</span></td>
                <td><span class="table-check">✓</span></td>
                <td><span class="table-check">✓</span></td>
              </tr>
              <tr>
                <td>Steam Room & Hot Showers</td>
                <td><span class="table-cross">—</span></td>
                <td><span class="table-check">✓ Unlimited</span></td>
                <td><span class="table-check">✓ Priority Access</span></td>
              </tr>
              <tr>
                <td>Lockers & Dressing Rooms</td>
                <td>Day Lockers</td>
                <td><span class="table-check">✓ Included</span></td>
                <td><span class="table-check">✓ Dedicated Locker</span></td>
              </tr>
              <tr>
                <td>Body Composition & BMI Check</td>
                <td>1 Initial Assessment</td>
                <td><span class="table-check">✓ Monthly Checkup</span></td>
                <td><span class="table-check">✓ Unlimited + Diet Plan</span></td>
              </tr>
              <tr>
                <td>Complimentary Guest Passes</td>
                <td><span class="table-cross">—</span></td>
                <td>2 Passes / Month</td>
                <td>Unlimited (1 Guest / Visit)</td>
              </tr>
              <tr>
                <td>Personal Trainer Sessions</td>
                <td>Available on Add-on</td>
                <td>1 Free Session</td>
                <td><span class="table-check">✓ 4 Sessions / Month</span></td>
              </tr>
              <tr>
                <td>WhatsApp & Reception Support</td>
                <td>Standard Hours</td>
                <td><span class="table-check">✓ Priority</span></td>
                <td><span class="table-check">✓ Dedicated Support</span></td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </section>

    <!-- ================= FAQ ACCORDION ================= -->
    <section class="section-spacing" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-metallic);">
      <div class="container-custom" style="max-width: 900px;">
        <div class="text-center mb-5">
          <span class="mono-tag mb-2">COMMON QUESTIONS</span>
          <h2 class="section-title">FREQUENTLY ASKED QUESTIONS</h2>
        </div>

        <div class="dm-accordion">
          <div class="accordion-item active">
            <div class="accordion-header">
              <h3 class="accordion-title">Are there any hidden fees or registration charges?</h3>
              <div class="accordion-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
              </div>
            </div>
            <div class="accordion-body" style="max-height: 200px;">
              <div class="accordion-content">
                No. What you see is what you pay. There are no surprise registration fees, maintenance fees, or hidden cancellation penalties. Monthly plans are simple and renewable month-to-month.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3 class="accordion-title">Can I try out the gym before taking a membership?</h3>
              <div class="accordion-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
              </div>
            </div>
            <div class="accordion-body">
              <div class="accordion-content">
                Yes! You can claim a free 1-day pass or schedule a quick gym tour with our coaches before deciding on a plan. Experience the machines, meet the trainers, and see the facility firsthand.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3 class="accordion-title">How does 24/7 gym access work?</h3>
              <div class="accordion-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
              </div>
            </div>
            <div class="accordion-body">
              <div class="accordion-content">
                Upon joining, you receive a secure digital QR pass or RFID keycard. This gives you seamless entry into the gym floor 24 hours a day, 365 days a year, whenever fits your schedule.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3 class="accordion-title">Can I pause or freeze my membership if I am traveling or ill?</h3>
              <div class="accordion-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
              </div>
            </div>
            <div class="accordion-body">
              <div class="accordion-content">
                Yes. You can freeze your membership for up to 30 days on monthly plans or up to 60 days on annual plans with zero extra charges. Simply message us on WhatsApp or speak with reception.
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <div class="accordion-header">
              <h3 class="accordion-title">Can I bring a guest or workout buddy with me?</h3>
              <div class="accordion-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
              </div>
            </div>
            <div class="accordion-body">
              <div class="accordion-content">
                Yes! Standard members receive 2 guest passes every month, while Elite VIP members enjoy unlimited guest passes (one guest per workout visit). Your guest enjoys full gym floor and steam room access.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Dynamic Pricing Data from MySQL injected for JS billing toggle -->
  <script id="serverPricingData" type="application/json">
    <?= json_encode($pricingData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
  </script>

<?php
require_once __DIR__ . '/includes/modals.php';
require_once __DIR__ . '/includes/footer.php';
?>
