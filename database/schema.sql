-- ==============================================================================
-- DARK MATTER — ELITE FITNESS
-- MySQL Database Schema (Phase 2 Backend)
-- Engine: InnoDB | Character Set: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `dark_matter_gym`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `dark_matter_gym`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. Table: admins
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'superadmin',
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_admin_username` (`username`),
  INDEX `idx_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. Table: membership_plans
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `membership_plans`;
CREATE TABLE `membership_plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `tier_label` VARCHAR(100) NOT NULL,
  `tagline` VARCHAR(255) NOT NULL,
  `monthly_price` DECIMAL(10, 2) NOT NULL,
  `annual_monthly_price` DECIMAL(10, 2) NOT NULL,
  `note_monthly` VARCHAR(255) NOT NULL,
  `note_annual` VARCHAR(255) NOT NULL,
  `is_popular` TINYINT(1) NOT NULL DEFAULT 0,
  `features` JSON NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_plan_slug` (`slug`),
  INDEX `idx_plan_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. Table: trainers
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `trainers`;
CREATE TABLE `trainers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `role_title` VARCHAR(150) NOT NULL,
  `specialties` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `credentials` JSON NOT NULL,
  `bio` TEXT NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_trainer_slug` (`slug`),
  INDEX `idx_trainer_category` (`category`),
  INDEX `idx_trainer_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. Table: programs
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `programs`;
CREATE TABLE `programs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `badge_text` VARCHAR(100) NOT NULL,
  `short_desc` TEXT NOT NULL,
  `intensity_score` DECIMAL(2, 1) NOT NULL,
  `intensity_display` VARCHAR(50) NOT NULL,
  `duration_minutes` INT NOT NULL,
  `burn_calories_range` VARCHAR(50) NOT NULL,
  `coach_ratio` VARCHAR(50) NOT NULL,
  `target_adaptation` TEXT NOT NULL,
  `gear_stations` TEXT NOT NULL,
  `recommended_cadence` VARCHAR(255) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_program_slug` (`slug`),
  INDEX `idx_program_category` (`category`),
  INDEX `idx_program_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. Table: gallery_images
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `gallery_images`;
CREATE TABLE `gallery_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `tag_label` VARCHAR(100) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `alt_text` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_gallery_category` (`category`),
  INDEX `idx_gallery_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. Table: enquiries (Passes, Tours, General Inquiries, Consultations)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `enquiries`;
CREATE TABLE `enquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `inquiry_type` ENUM('vip-pass', 'facility-tour', 'general-inquiry', 'trainer-consult', 'quick-pass', 'newsletter') NOT NULL,
  `preferred_date` DATE NULL,
  `preferred_time_slot` VARCHAR(50) NULL,
  `assigned_trainer` VARCHAR(100) NULL,
  `training_focus` VARCHAR(100) NULL,
  `message` TEXT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `status` ENUM('pending', 'verified', 'scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_enquiry_email` (`email`),
  INDEX `idx_enquiry_type` (`inquiry_type`),
  INDEX `idx_enquiry_status` (`status`),
  INDEX `idx_enquiry_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. Table: membership_applications
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `membership_applications`;
CREATE TABLE `membership_applications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT UNSIGNED NOT NULL,
  `plan_slug` VARCHAR(50) NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `billing_cycle` ENUM('monthly', 'annual') NOT NULL DEFAULT 'monthly',
  `preferred_start_date` DATE NULL,
  `payment_preference` VARCHAR(50) NOT NULL DEFAULT 'pay_at_gym',
  `amount_monthly` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'verified', 'active', 'rejected') NOT NULL DEFAULT 'pending',
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_app_plan` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE RESTRICT,
  INDEX `idx_app_email` (`email`),
  INDEX `idx_app_plan` (`plan_id`),
  INDEX `idx_app_status` (`status`),
  INDEX `idx_app_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 8. Table: site_settings
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 9. Table: members (Member Management Module)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `membership_id` VARCHAR(30) NOT NULL UNIQUE,
  `application_id` INT UNSIGNED NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL,
  `billing_cycle` ENUM('monthly', 'annual') NOT NULL DEFAULT 'monthly',
  `start_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `status` ENUM('active', 'expired', 'pending') NOT NULL DEFAULT 'active',
  `emergency_contact` VARCHAR(100) NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_member_plan` FOREIGN KEY (`plan_id`) REFERENCES `membership_plans` (`id`) ON DELETE RESTRICT,
  INDEX `idx_member_code` (`membership_id`),
  INDEX `idx_member_email` (`email`),
  INDEX `idx_member_status` (`status`),
  INDEX `idx_member_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 10. Table: member_payments
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `member_payments`;
CREATE TABLE `member_payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT UNSIGNED NOT NULL,
  `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` ENUM('UPI', 'Credit Card', 'Debit Card', 'Cash / Reception', 'Bank Transfer') NOT NULL DEFAULT 'UPI',
  `payment_status` ENUM('completed', 'pending', 'refunded', 'failed') NOT NULL DEFAULT 'completed',
  `payment_date` DATE NOT NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payment_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  INDEX `idx_payment_member` (`member_id`),
  INDEX `idx_payment_status` (`payment_status`),
  INDEX `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 11. Table: member_attendance
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `member_attendance`;
CREATE TABLE `member_attendance` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT UNSIGNED NOT NULL,
  `check_in_time` DATETIME NOT NULL,
  `check_out_time` DATETIME NULL,
  `method` VARCHAR(50) NOT NULL DEFAULT 'Biometric / QR Scan',
  `notes` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_att_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  INDEX `idx_att_member` (`member_id`),
  INDEX `idx_att_checkin` (`check_in_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

