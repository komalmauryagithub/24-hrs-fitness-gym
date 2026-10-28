<?php
/**
 * DARK MATTER — Elite Fitness
 * Database Migration: Member Management Module (Pillar 4)
 * Creates: members, member_payments, member_attendance
 * Alters: membership_applications (adds preferred_start_date, payment_preference)
 * Seeds initial demo data
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/Database.php';

use DarkMatter\Database;

try {
    $pdo = Database::getConnection();
    echo "Connected to database successfully.\n";

    // 1. Alter membership_applications if columns missing
    $cols = $pdo->query("SHOW COLUMNS FROM `membership_applications` LIKE 'preferred_start_date'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `membership_applications` ADD COLUMN `preferred_start_date` DATE NULL AFTER `billing_cycle`");
        echo "Added preferred_start_date to membership_applications.\n";
    }

    $cols = $pdo->query("SHOW COLUMNS FROM `membership_applications` LIKE 'payment_preference'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `membership_applications` ADD COLUMN `payment_preference` VARCHAR(50) NOT NULL DEFAULT 'pay_at_gym' AFTER `preferred_start_date`");
        echo "Added payment_preference to membership_applications.\n";
    }

    // 2. Create members table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `members` (
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
    ");
    echo "Table 'members' verified.\n";

    // 3. Create member_payments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `member_payments` (
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
    ");
    echo "Table 'member_payments' verified.\n";

    // 4. Create member_attendance table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `member_attendance` (
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
    ");
    echo "Table 'member_attendance' verified.\n";

    // 5. Seed sample demo members if table is empty
    $count = (int) $pdo->query("SELECT COUNT(*) FROM `members`")->fetchColumn();
    if ($count === 0) {
        $plans = $pdo->query("SELECT id, name FROM membership_plans ORDER BY id ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
        $planIds = array_keys($plans);
        $pInitiate = $planIds[0] ?? 1;
        $pObsidian = $planIds[1] ?? 2;
        $pBlack = $planIds[2] ?? 3;

        $membersData = [
            [
                'membership_id' => 'DM-2026-0101',
                'full_name' => 'Alexander Vance',
                'email' => 'a.vance@vanceholdings.com',
                'phone' => '+1 (555) 301-4491',
                'plan_id' => $pBlack,
                'plan_name' => $plans[$pBlack] ?? 'BLACK MATTER',
                'billing_cycle' => 'annual',
                'start_date' => date('Y-01-15'),
                'expiry_date' => date('Y-01-15', strtotime('+1 year')),
                'status' => 'active',
                'emergency_contact' => 'Elena Vance (+1 555-301-9988)',
                'notes' => 'Executive athlete. Requires biometric locker 14 and post-workout sauna suite reservation.',
                'payment_amount' => 2750.00,
                'payment_method' => 'Credit Card',
                'tx_id' => 'TXN-DM-20260115-0981'
            ],
            [
                'membership_id' => 'DM-2026-0102',
                'full_name' => 'Maya Lin Thorne',
                'email' => 'maya.thorne@metrolabs.io',
                'phone' => '+1 (555) 782-3901',
                'plan_id' => $pObsidian,
                'plan_name' => $plans[$pObsidian] ?? 'OBSIDIAN',
                'billing_cycle' => 'monthly',
                'start_date' => date('Y-m-d', strtotime('-45 days')),
                'expiry_date' => date('Y-m-d', strtotime('+15 days')),
                'status' => 'active',
                'emergency_contact' => 'David Thorne (+1 555-782-3902)',
                'notes' => 'Training for hybrid powerlifting competition. Uses 38F cold plunge daily.',
                'payment_amount' => 149.00,
                'payment_method' => 'UPI',
                'tx_id' => 'TXN-DM-20260312-5541'
            ],
            [
                'membership_id' => 'DM-2026-0103',
                'full_name' => 'Marcus Sterling',
                'email' => 'sterling.marcus@apexcapital.com',
                'phone' => '+1 (555) 234-8877',
                'plan_id' => $pObsidian,
                'plan_name' => $plans[$pObsidian] ?? 'OBSIDIAN',
                'billing_cycle' => 'monthly',
                'start_date' => date('Y-m-d', strtotime('-60 days')),
                'expiry_date' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'active',
                'emergency_contact' => 'Sarah Sterling (+1 555-234-8899)',
                'notes' => 'Assigned to Coach Viktor Stone for barbell strength progressions.',
                'payment_amount' => 149.00,
                'payment_method' => 'Credit Card',
                'tx_id' => 'TXN-DM-20260320-1129'
            ],
            [
                'membership_id' => 'DM-2026-0104',
                'full_name' => 'Dr. Elena Rostova',
                'email' => 'elena.rostova@neurolink.org',
                'phone' => '+1 (555) 891-2245',
                'plan_id' => $pInitiate,
                'plan_name' => $plans[$pInitiate] ?? 'INITIATE',
                'billing_cycle' => 'monthly',
                'start_date' => date('Y-m-d', strtotime('-90 days')),
                'expiry_date' => date('Y-m-d', strtotime('-5 days')),
                'status' => 'expired',
                'emergency_contact' => 'Boris Rostov (+1 555-891-2200)',
                'notes' => 'Renewal reminder dispatched. Expressed interest in upgrading to Obsidian tier.',
                'payment_amount' => 79.00,
                'payment_method' => 'Debit Card',
                'tx_id' => 'TXN-DM-20260120-7712'
            ],
            [
                'membership_id' => 'DM-2026-0105',
                'full_name' => 'Gabriel Cross',
                'email' => 'g.cross@crossathletics.com',
                'phone' => '+1 (555) 412-9018',
                'plan_id' => $pBlack,
                'plan_name' => $plans[$pBlack] ?? 'BLACK MATTER',
                'billing_cycle' => 'monthly',
                'start_date' => date('Y-m-d', strtotime('+3 days')),
                'expiry_date' => date('Y-m-d', strtotime('+33 days')),
                'status' => 'pending',
                'emergency_contact' => 'Rachel Cross (+1 555-412-9019)',
                'notes' => 'Online application completed. Biometric facial scan scheduled at concierge desk.',
                'payment_amount' => 289.00,
                'payment_method' => 'UPI',
                'tx_id' => 'TXN-DM-20260401-4409'
            ]
        ];

        $stmtMember = $pdo->prepare("
            INSERT INTO `members` (
                `membership_id`, `full_name`, `email`, `phone`, `plan_id`, `plan_name`,
                `billing_cycle`, `start_date`, `expiry_date`, `status`, `emergency_contact`, `notes`
            ) VALUES (
                :membership_id, :full_name, :email, :phone, :plan_id, :plan_name,
                :billing_cycle, :start_date, :expiry_date, :status, :emergency_contact, :notes
            )
        ");

        $stmtPay = $pdo->prepare("
            INSERT INTO `member_payments` (
                `member_id`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `payment_date`, `notes`
            ) VALUES (
                :member_id, :transaction_id, :amount, :payment_method, 'completed', :payment_date, :notes
            )
        ");

        $stmtAtt = $pdo->prepare("
            INSERT INTO `member_attendance` (
                `member_id`, `check_in_time`, `check_out_time`, `method`, `notes`
            ) VALUES (
                :member_id, :check_in, :check_out, 'Biometric Facial Gate', :notes
            )
        ");

        foreach ($membersData as $m) {
            $stmtMember->execute([
                ':membership_id'     => $m['membership_id'],
                ':full_name'         => $m['full_name'],
                ':email'             => $m['email'],
                ':phone'             => $m['phone'],
                ':plan_id'           => $m['plan_id'],
                ':plan_name'         => $m['plan_name'],
                ':billing_cycle'     => $m['billing_cycle'],
                ':start_date'        => $m['start_date'],
                ':expiry_date'       => $m['expiry_date'],
                ':status'            => $m['status'],
                ':emergency_contact' => $m['emergency_contact'],
                ':notes'             => $m['notes']
            ]);
            $memberId = (int) $pdo->lastInsertId();

            // Insert Payment
            $stmtPay->execute([
                ':member_id'       => $memberId,
                ':transaction_id'  => $m['tx_id'],
                ':amount'          => $m['payment_amount'],
                ':payment_method'  => $m['payment_method'],
                ':payment_date'    => $m['start_date'],
                ':notes'           => "Initial enrollment subscription ({$m['billing_cycle']})"
            ]);

            // Insert today's or recent attendance for active members
            if ($m['status'] === 'active') {
                $checkIn = date('Y-m-d H:i:s', strtotime('-2 hours'));
                $checkOut = ($m['membership_id'] === 'DM-2026-0101') ? null : date('Y-m-d H:i:s', strtotime('-45 minutes'));
                $notes = ($checkOut === null) ? 'Currently on training floor [Zone A / Iron Pit]' : 'Completed 75-min protocol & Cold Plunge';

                $stmtAtt->execute([
                    ':member_id'  => $memberId,
                    ':check_in'   => $checkIn,
                    ':check_out'  => $checkOut,
                    ':notes'      => $notes
                ]);
            }
        }
        echo "Seeded " . count($membersData) . " sample members, payment records, and attendance logs.\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
