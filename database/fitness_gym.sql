-- 24HRS FITNESS — Clean Database Dump
-- Generated: 2026-10-27 10:28:14
-- Database: fitness_gym

CREATE DATABASE IF NOT EXISTS `fitness_gym` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fitness_gym`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'superadmin',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_admin_username` (`username`),
  KEY `idx_admin_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` VALUES
('1', 'fitness_gym', 'fitness_gym@gmail.com', '$2y$10$qE0bcYz/rPgH663NXtzUheOchuhGHiZDHZsWWnapXCsxK3f.kHC5S', 'superadmin', '2026-10-27 15:12:27', '2026-10-27 01:42:44', '2026-10-27 15:12:27'),
('2', 'darkmatter_admin', 'admin@darkmatter-fitness.com', '$2y$12$DXxMP7vpyxZQtUdTz5gZM.TWR0T0xy36PxW/jYhvfCw8Ed9wuik7K', 'superadmin', '2026-10-27 14:59:30', '2026-10-27 14:57:14', '2026-10-27 14:59:30');

DROP TABLE IF EXISTS `enquiries`;
CREATE TABLE `enquiries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `inquiry_type` enum('vip-pass','facility-tour','general-inquiry','trainer-consult','quick-pass','newsletter') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time_slot` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_trainer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `training_focus` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','verified','scheduled','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enquiry_email` (`email`),
  KEY `idx_enquiry_type` (`inquiry_type`),
  KEY `idx_enquiry_status` (`status`),
  KEY `idx_enquiry_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `gallery_images`;
CREATE TABLE `gallery_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag_label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery_category` (`category`),
  KEY `idx_gallery_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gallery_images` VALUES
('13', 'Strength & Free Weights Area', 'iron', 'WEIGHT TRAINING', 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=800&auto=format&fit=crop', 'Dumbbells and power lifting cages', '1', '1', '2026-10-27 15:57:13'),
('14', 'Modern Cardio & Treadmills Zone', 'suites', 'CARDIO ZONE', 'https://images.unsplash.com/photo-1538805060514-97d9cc17730c?q=80&w=800&auto=format&fit=crop', 'Commercial treadmills and cardio machines', '2', '1', '2026-10-27 15:57:13'),
('15', 'Hot Steam Room & Finnish Sauna', 'spa', 'STEAM & SAUNA', 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=800&auto=format&fit=crop', 'Cedar wood dry sauna and steam facilities', '3', '1', '2026-10-27 15:57:13'),
('16', 'Crossfit Sprint & Sled Turf', 'turf', 'CROSSFIT TURF', 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?q=80&w=800&auto=format&fit=crop', 'Sprint turf with heavy sleds and battle ropes', '4', '1', '2026-10-27 15:57:13'),
('17', 'Boxing Bags & Agility Ring', 'turf', 'BOXING AREA', 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?q=80&w=800&auto=format&fit=crop', 'Heavy punching bags and boxing ring', '5', '1', '2026-10-27 15:57:13'),
('18', 'Clean Changing & Locker Rooms', 'suites', 'LOCKER ROOMS', 'https://images.unsplash.com/photo-1584464491033-06628f3a6b7b?q=80&w=800&auto=format&fit=crop', 'Hygienic locker facilities and hot showers', '6', '1', '2026-10-27 15:57:13');

DROP TABLE IF EXISTS `member_attendance`;
CREATE TABLE `member_attendance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int unsigned NOT NULL,
  `check_in_time` datetime NOT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Biometric / QR Scan',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_att_member` (`member_id`),
  KEY `idx_att_checkin` (`check_in_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `member_attendance` VALUES
('1', '1', '2026-10-27 06:02:59', NULL, 'Biometric Facial Gate', 'Currently on training floor [Zone A / Iron Pit]', '2026-10-27 13:32:59'),
('2', '2', '2026-10-27 06:02:59', '2026-10-27 07:17:59', 'Biometric Facial Gate', 'Completed 75-min protocol & Cold Plunge', '2026-10-27 13:32:59'),
('3', '3', '2026-10-27 06:02:59', '2026-10-27 07:17:59', 'Biometric Facial Gate', 'Completed 75-min protocol & Cold Plunge', '2026-10-27 13:32:59');

DROP TABLE IF EXISTS `member_payments`;
CREATE TABLE `member_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int unsigned NOT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('UPI','Credit Card','Debit Card','Cash / Reception','Bank Transfer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UPI',
  `payment_status` enum('completed','pending','refunded','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `payment_date` date NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `idx_payment_member` (`member_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `member_payments` VALUES
('1', '1', 'TXN-DM-20260115-0981', '24000.00', 'Credit Card', 'completed', '2026-01-15', 'Annual subscription [PRO FITNESS plan]', '2026-10-27 13:32:59'),
('2', '2', 'TXN-DM-20260312-5541', '2500.00', 'UPI', 'completed', '2026-09-12', 'Monthly fee payment [PRO FITNESS plan]', '2026-10-27 13:32:59'),
('3', '3', 'TXN-DM-20260320-1129', '1500.00', 'Debit Card', 'completed', '2026-08-28', 'Monthly fee payment [STARTER plan]', '2026-10-27 13:32:59'),
('4', '4', 'TXN-DM-20260120-7712', '1500.00', 'Cash / Reception', 'completed', '2026-07-29', 'Monthly renewal payment [STARTER plan]', '2026-10-27 13:32:59'),
('5', '5', 'TXN-DM-20260401-4409', '4500.00', 'UPI', 'completed', '2026-10-30', 'Monthly fee payment [ELITE VIP plan]', '2026-10-27 13:32:59'),
('6', '6', 'TXN-DM-20261027-68ADF0', '43200.00', 'UPI', 'completed', '2026-10-27', 'Instant online checkout [annual ELITE VIP plan]', '2026-10-27 14:06:51');

DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `membership_id` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_id` int unsigned DEFAULT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` int unsigned NOT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_cycle` enum('monthly','annual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('active','expired','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `emergency_contact` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_id` (`membership_id`),
  KEY `fk_member_plan` (`plan_id`),
  KEY `idx_member_code` (`membership_id`),
  KEY `idx_member_email` (`email`),
  KEY `idx_member_status` (`status`),
  KEY `idx_member_expiry` (`expiry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `members` VALUES
('1', 'DM-2026-0101', NULL, 'Vikram Malhotra', 'vikram.malhotra@gmail.com', '+91 98201 11223', '11', 'PRO FITNESS', 'annual', '2026-01-15', '2027-01-15', 'active', 'Sunita Malhotra (+91 98201 11224)', 'Flagship athlete. Requires biometric locker 14 and post-workout steam sauna access.', '2026-10-27 13:32:59', '2026-10-27 13:32:59'),
('2', 'DM-2026-0102', NULL, 'Ananya Deshmukh', 'ananya.deshmukh@gmail.com', '+91 98192 33445', '11', 'PRO FITNESS', 'monthly', '2026-09-12', '2026-11-11', 'active', 'Rajesh Deshmukh (+91 98192 33446)', 'Focusing on strength progressions and cardio stamina.', '2026-10-27 13:32:59', '2026-10-27 13:32:59'),
('3', 'DM-2026-0103', NULL, 'Rohan Mehta', 'rohan.mehta@techminds.io', '+91 98334 55667', '10', 'STARTER', 'monthly', '2026-08-28', '2026-11-26', 'active', 'Pooja Mehta (+91 98334 55668)', '24/7 late night workouts. Calisthenics and free weights.', '2026-10-27 13:32:59', '2026-10-27 13:32:59'),
('4', 'DM-2026-0104', NULL, 'Dr. Priya Sharma', 'priya.sharma@neurolink.in', '+91 98210 66778', '10', 'STARTER', 'monthly', '2026-07-29', '2026-10-22', 'expired', 'Dr. Amit Sharma (+91 98210 66779)', 'Renewal reminder sent. Interested in upgrading to PRO FITNESS tier.', '2026-10-27 13:32:59', '2026-10-27 13:32:59'),
('5', 'DM-2026-0105', NULL, 'Kabir Kapoor', 'kabir.kapoor@kholdings.in', '+91 99302 77889', '12', 'ELITE VIP', 'monthly', '2026-10-25', '2026-11-25', 'active', 'Simran Kapoor (+91 99302 77890)', 'VIP coaching program enrolled. Assigned to senior personal trainer.', '2026-10-27 13:32:59', '2026-10-27 13:32:59'),
('6', 'DM-2026-0005', '5', 'Neha Verma', 'neha.verma@fashionstudio.com', '+91 99201 88990', '12', 'ELITE VIP', 'annual', '2026-10-28', '2027-10-28', 'active', 'Primary: +91 99201 88990', 'Online self-enrollment (UPI) via 24HRS FITNESS Website.', '2026-10-27 14:06:51', '2026-10-27 14:06:51');

DROP TABLE IF EXISTS `membership_applications`;
CREATE TABLE `membership_applications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` int unsigned NOT NULL,
  `plan_slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_cycle` enum('monthly','annual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `preferred_start_date` date DEFAULT NULL,
  `payment_preference` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pay_at_gym',
  `amount_monthly` decimal(10,2) NOT NULL,
  `status` enum('pending','verified','active','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_app_email` (`email`),
  KEY `idx_app_plan` (`plan_id`),
  KEY `idx_app_status` (`status`),
  KEY `idx_app_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `membership_applications` VALUES
('1', '2', 'obsidian', 'OBSIDIAN', 'Sophia Audit Athlete', 'sophia.audit@darkmatter-fitness.com', '+1 (555) 888-4321', 'District 7 — Flagship (400 Obsidian Blvd)', 'monthly', NULL, 'pay_at_gym', '149.00', 'pending', '127.0.0.1', 'Unknown', '2026-10-27 02:09:04'),
('2', '2', 'obsidian', 'OBSIDIAN', 'Sophia Audit Athlete', 'sophia.audit@darkmatter-fitness.com', '+1 (555) 888-4321', 'District 7 — Flagship (400 Obsidian Blvd)', 'monthly', NULL, 'pay_at_gym', '149.00', 'pending', '127.0.0.1', 'Unknown', '2026-10-27 02:09:54'),
('3', '2', 'obsidian', 'OBSIDIAN', 'Sophia Audit Athlete', 'sophia.audit@darkmatter-fitness.com', '+1 (555) 888-4321', 'District 7 — Flagship (400 Obsidian Blvd)', 'monthly', NULL, 'pay_at_gym', '149.00', 'pending', '127.0.0.1', 'Unknown', '2026-10-27 02:13:43'),
('4', '2', 'obsidian', 'OBSIDIAN', 'Alexander Test Athlete', 'alex.test@example.com', '+1 (555) 123-4567', 'District 7 — Flagship (400 Obsidian Blvd)', 'monthly', '2026-11-01', 'pay_at_gym', '149.00', 'pending', '::1', 'Unknown', '2026-10-27 14:06:51'),
('5', '3', 'black-matter', 'BLACK MATTER', 'Sophia Cross VIP', 'sophia.vip@example.com', '+1 (555) 987-6543', 'District 7 — Flagship (400 Obsidian Blvd)', 'annual', '2026-10-28', 'online_instant', '231.00', 'active', '::1', 'Unknown', '2026-10-27 14:06:51');

DROP TABLE IF EXISTS `membership_plans`;
CREATE TABLE `membership_plans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier_label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagline` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `annual_monthly_price` decimal(10,2) NOT NULL,
  `note_monthly` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_annual` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_popular` tinyint(1) NOT NULL DEFAULT '0',
  `features` json NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_plan_slug` (`slug`),
  KEY `idx_plan_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `membership_plans` VALUES
('10', 'starter', 'STARTER', 'BASIC PLAN', 'Full gym floor and cardio access for regular workouts.', '1500.00', '1200.00', 'Billed monthly • Cancel anytime', 'Billed annually (₹14,400/yr) • Save 20%', '0', '[\"24/7 Gym Floor Access\", \"Free Weights & Machines Area\", \"Cardio Zone Access\", \"Locker Room & Hot Showers\", \"Free Fitness Assessment\"]', '1', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('11', 'pro-fitness', 'PRO FITNESS', 'POPULAR CHOICE', 'Complete access to weights, cardio zones, and steam & sauna recovery.', '2500.00', '2000.00', 'Billed monthly • Most popular plan', 'Billed annually (₹24,000/yr) • Save 20%', '1', '[\"24/7 Unlimited Gym Access\", \"Full Strength & Cardio Areas\", \"Steam Room & Sauna Recovery\", \"2 Free Guest Passes / Month\", \"1 Personal Trainer Session\", \"Nutrition & Diet Guidelines\"]', '2', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('12', 'elite-vip', 'ELITE VIP', 'ALL-INCLUSIVE VIP', 'Premium access with dedicated 1-on-1 personal coaching and unlimited recovery.', '4500.00', '3600.00', 'Billed monthly • Complete VIP access', 'Billed annually (₹43,200/yr) • Save 20%', '0', '[\"24/7 VIP Access Every Day\", \"Full Gym, Cardio & Crossfit Zone\", \"Unlimited Steam & Sauna Access\", \"4 Personal Training Sessions / Month\", \"Dedicated Private Locker\", \"Unlimited Guest Passes\", \"Custom Diet & Workout Plan\"]', '3', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13');

DROP TABLE IF EXISTS `programs`;
CREATE TABLE `programs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `badge_text` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `intensity_score` decimal(2,1) NOT NULL,
  `intensity_display` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_minutes` int NOT NULL,
  `burn_calories_range` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `coach_ratio` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_adaptation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gear_stations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommended_cadence` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_program_slug` (`slug`),
  KEY `idx_program_category` (`category`),
  KEY `idx_program_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `programs` VALUES
('14', 'hypertrophy-strength', 'Strength & Muscle Building', 'strength', 'WEIGHT TRAINING', 'Build lean muscle mass and increase your strength with guided barbell, dumbbell, and machine routines.', '5.0', '5 / 5 [HIGH LOAD]', '75', '600 – 850 kcal', '1 Trainer : 6 Members', 'Muscle building, strength gains, and bone density.', 'Power racks, Olympic bars, dumbbells up to 50kg, and cable stations.', '3 to 4 workouts / week', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop', '1', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('15', 'metabolic-conditioning', 'Cardio & Fat Burn (HIIT)', 'cardio', 'FAT LOSS & CARDIO', 'High energy cardio intervals to burn maximum calories, improve stamina, and accelerate metabolism.', '4.5', '4.5 / 5 [HIGH ENERGY]', '50', '750 – 1,000 kcal', '1 Trainer : 8 Members', 'Fat loss, cardiovascular endurance, and stamina.', 'Curved treadmills, rowing machines, air bikes, and indoor turf.', '2 to 3 workouts / week', 'https://images.unsplash.com/photo-1538805060514-97d9cc17730c?q=80&w=800&auto=format&fit=crop', '2', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('16', 'cryo-thermal-recovery', 'Steam & Sauna Recovery', 'recovery', 'RELAXATION & WELLNESS', 'Relax tight muscles, relieve workout soreness, and de-stress with hot steam and dry Finnish cedar saunas.', '1.0', '1 / 5 [PASSIVE RECOVERY]', '45', '150 – 250 kcal', 'Attendant on duty', 'Muscle relaxation, stress relief, and post-workout recovery.', 'Hot steam rooms, dry Finnish cedar saunas, and hot showers.', 'After heavy workouts or rest days', 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=800&auto=format&fit=crop', '3', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('17', 'combat-conditioning', 'Boxing & Combat Fitness', 'functional', 'BOXING & AGILITY', 'Learn striking combos, agility footwork, and core conditioning with heavy bags and speed drills.', '4.5', '4.5 / 5 [HIGH ENERGY]', '60', '700 – 950 kcal', '1 Trainer : 6 Members', 'Punching power, agility, reflex speed, and core strength.', 'Heavy bags, speed ropes, focus pads, and boxing turf.', '2 to 3 workouts / week', 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?q=80&w=800&auto=format&fit=crop', '4', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('18', 'functional-calisthenics', 'Calisthenics & Mobility', 'functional', 'BODYWEIGHT & CORE', 'Master pull-ups, dips, push-ups, abdominal core stability, and full body mobility.', '3.5', '3.5 / 5 [CORE CONTROL]', '55', '450 – 600 kcal', '1 Trainer : 8 Members', 'Bodyweight strength, core endurance, and joint flexibility.', 'Pull-up bars, parallel bars, gymnastic rings, and stretch bands.', '3 workouts / week', 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?q=80&w=800&auto=format&fit=crop', '5', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13');

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` VALUES
('1', 'site_name', '24HRS FITNESS', 'Official brand brand title', '2026-10-27 15:56:36'),
('2', 'site_tagline', '24/7 Premium Gym & Physical Fitness Club', 'Brand headline manifesto', '2026-10-27 15:56:36'),
('3', 'contact_email', 'contact@24hrsfitness.com', 'Primary concierge email address', '2026-10-27 15:56:36'),
('4', 'contact_phone', '+91 99201 20785', 'Primary front desk contact number', '2026-10-27 15:56:36'),
('5', 'address', 'Plot 42, Commercial Avenue, Opp. Metro Station, Andheri West, Mumbai, MH 400053', 'Physical flagship address', '2026-10-27 15:56:36'),
('6', 'geo_coordinates', '19.1136° N, 72.8697° E', 'Flagship GPS telemetry coordinates', '2026-10-27 15:56:36'),
('7', 'whatsapp_number', '919920120785', 'Direct encrypted concierge WhatsApp number', '2026-10-27 15:56:36'),
('8', 'operating_hours_members', 'Open 24 Hours / 7 Days a Week', 'Member entry schedule', '2026-10-27 15:56:36'),
('9', 'operating_hours_staff', '06:00 AM – 10:00 PM Daily', 'Staffed front desk hours', '2026-10-27 15:56:36'),
('10', 'active_member_cap', '500', 'Maximum active members to eliminate overcrowding', '2026-10-27 01:42:44'),
('11', 'current_active_members', '385', 'Current active registered members', '2026-10-27 15:56:36'),
('12', 'facility_sq_ft', '25,000', 'Total square footage of training floor', '2026-10-27 15:56:36');

DROP TABLE IF EXISTS `trainers`;
CREATE TABLE `trainers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialties` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials` json NOT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_trainer_slug` (`slug`),
  KEY `idx_trainer_category` (`category`),
  KEY `idx_trainer_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trainers` VALUES
('9', 'elena-rostova', 'ELENA ROSTOVA', 'Head Cardio & Fat Loss Coach', 'Fat Loss, HIIT, Cardio Stamina, Endurance', 'metabolic', '[\"ACE Certified Personal Trainer\", \"HIIT Specialist\", \"CPR / AED Certified\"]', 'Certified trainer with 8+ years coaching members in fat-loss HIIT workouts, cardiovascular stamina, and personalized body transformation.', 'https://images.unsplash.com/photo-1594381898411-846e7d193883?q=80&w=800&auto=format&fit=crop', '1', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('10', 'siobhan-kelly', 'SIOBHAN KELLY', 'Strength & Weightlifting Coach', 'Powerlifting, Muscle Hypertrophy, Squat/Bench/Deadlift', 'strength', '[\"CSCS Strength Coach\", \"USAW Sports Coach\", \"B.Sc. Exercise Science\"]', 'Specializes in proper barbell lifting mechanics, safe progressive overload, and building solid muscle mass for all experience levels.', 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=800&auto=format&fit=crop', '2', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('11', 'darius-thorne', 'DARIUS THORNE', 'Boxing & Crossfit Coach', 'Boxing Combos, Agility, Core Conditioning, Crossfit', 'combat', '[\"Certified Boxing Instructor\", \"CrossFit L2 Coach\", \"Agility Specialist\"]', 'Former golden gloves boxer and functional conditioning coach. Teaches punching combinations, agility footwork, and high-energy conditioning.', 'https://images.unsplash.com/photo-1567013127542-490d757e51fc?q=80&w=800&auto=format&fit=crop', '3', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('12', 'dr-henrik-lindqvist', 'DR. HENRIK LINDQVIST', 'Fitness & Nutrition Specialist', 'Nutrition Planning, Body Composition, Injury Recovery', 'physiology', '[\"Registered Dietitian (RD)\", \"Precision Nutrition L2\", \"Sports Physiologist\"]', 'Guidance on balanced nutrition, healthy fat loss without starvation, body composition tracking, and post-workout recovery strategies.', 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?q=80&w=800&auto=format&fit=crop', '4', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13'),
('13', 'maya-sterling', 'MAYA STERLING', 'Calisthenics & Mobility Trainer', 'Bodyweight Training, Pull-ups, Core Strength, Flexibility', 'combat', '[\"World Calisthenics Org L2\", \"Yoga 200hr RYT\", \"Mobility Specialist\"]', 'Passionate about bodyweight mastery. Helps members achieve their first strict pull-up, master core stability, and improve overall flexibility.', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=800&auto=format&fit=crop', '5', '1', '2026-10-27 15:57:13', '2026-10-27 15:57:13');

SET FOREIGN_KEY_CHECKS = 1;
