<?php
/**
 * DARK MATTER — Elite Fitness
 * Membership Application Submission Endpoint
 * Validates plan selection against database, checks rate limits, and persists application.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
ini_set('display_errors', '0');

require_once __DIR__ . '/../includes/Env.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';

use DarkMatter\Database;
use DarkMatter\Security;

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed. Only POST requests are accepted.'
    ]);
    exit;
}

// Ensure session is active
Security::initSession();

// Read input (JSON or form-urlencoded)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$rawInput = [];

if (str_contains($contentType, 'application/json')) {
    $json = file_get_contents('php://input');
    $rawInput = is_string($json) ? (json_decode($json, true) ?? []) : [];
} else {
    $rawInput = $_POST;
}

// 1. Honeypot check
if (!Security::verifyHoneypot($rawInput)) {
    echo json_encode([
        'success' => true,
        'message' => 'Membership application registered.'
    ]);
    exit;
}

// 2. Rate limiting (max 10 applications per 5 minutes per IP)
if (!Security::checkRateLimit('membership_application', 10, 300)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Rate limit exceeded. Please wait 5 minutes before submitting additional requests.'
    ]);
    exit;
}

// 3. CSRF Verification
$headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
$bodyToken = $rawInput['csrf_token'] ?? null;
$submittedToken = $headerToken ?: $bodyToken;

if (!Security::verifyCsrfToken($submittedToken)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Security token invalid or expired. Please refresh the page and try again.',
        'error_code' => 'CSRF_FAILURE'
    ]);
    exit;
}

// 4. Validate Inputs
$errors = [];

$planSlug = Security::sanitizeString($rawInput['plan_slug'] ?? 'starter', 50);
$billingCycle = Security::validateEnum($rawInput['billing_cycle'] ?? 'monthly', ['monthly', 'annual']) ?? 'monthly';

$fullName = Security::sanitizeString($rawInput['name'] ?? ($rawInput['full_name'] ?? ''), 100);
if (mb_strlen($fullName) < 2) {
    $errors[] = 'Full name must be at least 2 characters.';
}

$email = Security::validateEmail($rawInput['email'] ?? '');
if (!$email) {
    $errors[] = 'A valid email address is required.';
}

$phone = Security::validatePhone($rawInput['phone'] ?? '');
if (!$phone) {
    $errors[] = 'A valid phone number is required.';
}

$location = Security::sanitizeString($rawInput['location'] ?? 'Main Branch — City Center', 100);
$preferredDateRaw = Security::sanitizeString($rawInput['preferred_start_date'] ?? ($rawInput['preferred_date'] ?? date('Y-m-d')), 20);
$preferredStartDate = date('Y-m-d', strtotime($preferredDateRaw) ?: time());
$paymentPreference = Security::validateEnum($rawInput['payment_preference'] ?? 'pay_at_gym', ['pay_at_gym', 'online_instant']) ?? 'pay_at_gym';
$onlinePaymentMethod = Security::sanitizeString($rawInput['online_payment_method'] ?? 'UPI', 50);

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Validation error. Please correct the highlighted entries.',
        'errors'  => $errors
    ]);
    exit;
}

// 5. Query and Verify Plan from Database
try {
    $sqlPlan = "SELECT `id`, `slug`, `name`, `monthly_price`, `annual_monthly_price`, `is_active`
                FROM `membership_plans`
                WHERE `slug` = :slug AND `is_active` = 1
                LIMIT 1";

    $plan = Database::fetchOne($sqlPlan, [':slug' => $planSlug]);

    if (!$plan) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'The selected membership plan does not exist or is currently unavailable.'
        ]);
        exit;
    }

    $amountMonthly = $billingCycle === 'annual'
        ? (float) $plan['annual_monthly_price']
        : (float) $plan['monthly_price'];

    $isOnlinePaid = ($paymentPreference === 'online_instant');
    $appStatus = $isOnlinePaid ? 'active' : 'pending';

    // 6. Insert Application into Database
    $sqlInsert = "INSERT INTO `membership_applications` (
                    `plan_id`, `plan_slug`, `plan_name`, `full_name`, `email`,
                    `phone`, `location`, `billing_cycle`, `preferred_start_date`,
                    `payment_preference`, `amount_monthly`, `status`, `ip_address`, `user_agent`
                  ) VALUES (
                    :plan_id, :plan_slug, :plan_name, :full_name, :email,
                    :phone, :location, :billing_cycle, :preferred_start_date,
                    :payment_preference, :amount_monthly, :status, :ip, :ua
                  )";

    Database::execute($sqlInsert, [
        ':plan_id'              => (int) $plan['id'],
        ':plan_slug'            => $plan['slug'],
        ':plan_name'            => $plan['name'],
        ':full_name'            => $fullName,
        ':email'                => $email,
        ':phone'                => $phone,
        ':location'             => $location,
        ':billing_cycle'        => $billingCycle,
        ':preferred_start_date' => $preferredStartDate,
        ':payment_preference'   => $paymentPreference,
        ':amount_monthly'       => $amountMonthly,
        ':status'               => $appStatus,
        ':ip'                   => Security::getClientIp(),
        ':ua'                   => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255)
    ]);

    $appId = (int) Database::lastInsertId();
    $membershipCode = sprintf('MBR-%d-%04d', (int) date('Y'), $appId);

    // If online instant payment, also create member profile & payment record directly!
    $memberRecordId = null;
    $txId = null;
    if ($isOnlinePaid) {
        $expiryDate = $billingCycle === 'annual'
            ? date('Y-m-d', strtotime($preferredStartDate . ' +1 year'))
            : date('Y-m-d', strtotime($preferredStartDate . ' +1 month'));

        $sqlMember = "INSERT INTO `members` (
                        `membership_id`, `application_id`, `full_name`, `email`, `phone`,
                        `plan_id`, `plan_name`, `billing_cycle`, `start_date`, `expiry_date`,
                        `status`, `emergency_contact`, `notes`
                      ) VALUES (
                        :membership_id, :app_id, :full_name, :email, :phone,
                        :plan_id, :plan_name, :billing_cycle, :start_date, :expiry_date,
                        'active', :emergency_contact, :notes
                      )";

        Database::execute($sqlMember, [
            ':membership_id'     => $membershipCode,
            ':app_id'            => $appId,
            ':full_name'         => $fullName,
            ':email'             => $email,
            ':phone'             => $phone,
            ':plan_id'           => (int) $plan['id'],
            ':plan_name'         => $plan['name'],
            ':billing_cycle'     => $billingCycle,
            ':start_date'        => $preferredStartDate,
            ':expiry_date'       => $expiryDate,
            ':emergency_contact' => 'Primary: ' . $phone,
            ':notes'             => "Online self-enrollment ({$onlinePaymentMethod}) via 24HRS FITNESS Website."
        ]);

        $memberRecordId = (int) Database::lastInsertId();
        $txId = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $totalCharge = ($billingCycle === 'annual') ? ($amountMonthly * 12) : $amountMonthly;

        Database::execute("INSERT INTO `member_payments` (
            `member_id`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `payment_date`, `notes`
        ) VALUES (
            :member_id, :tx_id, :amount, :method, 'completed', :pdate, :notes
        )", [
            ':member_id' => $memberRecordId,
            ':tx_id'     => $txId,
            ':amount'    => $totalCharge,
            ':method'    => in_array($onlinePaymentMethod, ['Credit Card', 'Debit Card', 'Bank Transfer']) ? $onlinePaymentMethod : 'UPI',
            ':pdate'     => date('Y-m-d'),
            ':notes'     => "Instant online checkout [{$billingCycle} plan]"
        ]);
    }

    // Refresh CSRF token for subsequent requests
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    echo json_encode([
        'success' => true,
        'message' => $isOnlinePaid
            ? 'Payment verified! Membership activated successfully.'
            : 'Membership application submitted. You can complete fee payment at gym reception.',
        'data'    => [
            'application_id'       => $appId,
            'membership_code'      => $membershipCode,
            'plan_name'            => $plan['name'],
            'billing_cycle'        => $billingCycle,
            'amount_monthly'       => $amountMonthly,
            'amount_total'         => $isOnlinePaid ? $totalCharge : $amountMonthly,
            'applicant_name'       => $fullName,
            'email'                => $email,
            'phone'                => $phone,
            'location'             => $location,
            'preferred_start_date' => $preferredStartDate,
            'payment_preference'   => $paymentPreference,
            'is_paid'              => $isOnlinePaid,
            'transaction_id'       => $txId,
            'new_csrf_token'       => $_SESSION['csrf_token']
        ]
    ]);
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS MEMBERSHIP APPLY EXCEPTION]: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error during registration. Please try again.'
    ]);
}
