<?php
/**
 * DARK MATTER — Elite Fitness
 * Public Enquiry & Pass Submission Endpoint
 * Handles VIP passes, tours, inquiries, consultations, and briefings.
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

// Ensure session is initialized
Security::initSession();

// Read input (support both application/json and application/x-www-form-urlencoded)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$rawInput = [];

if (str_contains($contentType, 'application/json')) {
    $json = file_get_contents('php://input');
    $rawInput = is_string($json) ? (json_decode($json, true) ?? []) : [];
} else {
    $rawInput = $_POST;
}

// 1. Honeypot Anti-Spam Check
if (!Security::verifyHoneypot($rawInput)) {
    // Silently return pseudo-success to trap spambots
    echo json_encode([
        'success' => true,
        'message' => 'Transmission received.'
    ]);
    exit;
}

// 2. Sliding Window Rate Limiting (max 10 submissions per 5 minutes per IP)
if (!Security::checkRateLimit('enquiry_submission', 10, 300)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Rate limit exceeded. Please wait 5 minutes before submitting additional requests.'
    ]);
    exit;
}

// 3. CSRF Verification
// Token can be sent in header 'X-CSRF-Token' or body 'csrf_token'
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

// 4. Determine Inquiry Type
$allowedTypes = ['vip-pass', 'facility-tour', 'general-inquiry', 'trainer-consult', 'quick-pass', 'newsletter'];
$inquiryType = Security::validateEnum($rawInput['inquiry_type'] ?? 'general-inquiry', $allowedTypes) ?? 'general-inquiry';

$errors = [];

// Special handling for Newsletter briefing
if ($inquiryType === 'newsletter') {
    $email = Security::validateEmail($rawInput['email'] ?? '');
    if (!$email) {
        $errors[] = 'A valid email address is required.';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    try {
        $sql = "INSERT INTO `enquiries` (`full_name`, `email`, `phone`, `location`, `inquiry_type`, `ip_address`, `user_agent`, `status`)
                VALUES (:full_name, :email, 'N/A', 'Main Branch — City Center', 'newsletter', :ip, :ua, 'pending')";

        Database::execute($sql, [
            ':full_name' => 'Newsletter Subscriber',
            ':email'     => $email,
            ':ip'        => Security::getClientIp(),
            ':ua'        => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255)
        ]);

        echo json_encode([
            'success' => true,
            'message' => "Newsletter subscription confirmed for {$email}."
        ]);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal processing error. Please try again shortly.'
        ]);
        exit;
    }
}

// Common Field Validations
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
if (empty($location)) {
    $location = 'Main Branch — City Center';
}

// Mode-Specific Fields
$preferredDate = null;
if ($inquiryType === 'vip-pass') {
    $preferredDate = Security::validateDate($rawInput['preferred_date'] ?? '');
    if (!$preferredDate) {
        $errors[] = 'Please select a valid intended visit date.';
    } elseif ($preferredDate < date('Y-m-d')) {
        $errors[] = 'Visit date cannot be in the past.';
    }
}

$preferredTimeSlot = null;
if ($inquiryType === 'facility-tour') {
    $allowedSlots = ['morning', 'midday', 'evening', 'night'];
    $preferredTimeSlot = Security::validateEnum($rawInput['preferred_time_slot'] ?? '', $allowedSlots);
    if (!$preferredTimeSlot) {
        $errors[] = 'Please select a preferred tour time slot.';
    }
}

$assignedTrainer = null;
$trainingFocus = null;
if ($inquiryType === 'trainer-consult') {
    $assignedTrainer = Security::sanitizeString($rawInput['assigned_trainer'] ?? 'Trainer', 100);
    $trainingFocus = Security::sanitizeString($rawInput['training_focus'] ?? 'General Fitness', 100);
}

$message = Security::sanitizeString($rawInput['message'] ?? '', 1000);

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Validation error. Please correct the highlighted entries.',
        'errors'  => $errors
    ]);
    exit;
}

// 5. Insert Record using PDO Prepared Statement
try {
    $sql = "INSERT INTO `enquiries` (
                `full_name`, `email`, `phone`, `location`, `inquiry_type`,
                `preferred_date`, `preferred_time_slot`, `assigned_trainer`,
                `training_focus`, `message`, `ip_address`, `user_agent`, `status`
            ) VALUES (
                :full_name, :email, :phone, :location, :inquiry_type,
                :preferred_date, :preferred_time_slot, :assigned_trainer,
                :training_focus, :message, :ip, :ua, 'pending'
            )";

    Database::execute($sql, [
        ':full_name'           => $fullName,
        ':email'               => $email,
        ':phone'               => $phone,
        ':location'            => $location,
        ':inquiry_type'        => $inquiryType,
        ':preferred_date'      => $preferredDate,
        ':preferred_time_slot' => $preferredTimeSlot,
        ':assigned_trainer'    => $assignedTrainer,
        ':training_focus'      => $trainingFocus,
        ':message'             => $message ?: null,
        ':ip'                  => Security::getClientIp(),
        ':ua'                  => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255)
    ]);

    $insertedId = (int) Database::lastInsertId();
    $referenceCode = sprintf('GYM-%d-%04d', (int) date('Y'), $insertedId);

    // Refresh CSRF token on successful write
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    echo json_encode([
        'success' => true,
        'message' => 'Request received successfully. Our gym team will contact you shortly.',
        'data'    => [
            'enquiry_id'     => $insertedId,
            'reference_code' => $referenceCode,
            'athlete_name'   => $fullName,
            'inquiry_type'   => $inquiryType,
            'email'          => $email,
            'new_csrf_token' => $_SESSION['csrf_token']
        ]
    ]);
} catch (\Throwable $e) {
    error_log('[24HRS FITNESS API ENQUIRY EXCEPTION]: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A database error occurred during storage. Please try again.'
    ]);
}
