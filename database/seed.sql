-- ==============================================================================
-- DARK MATTER — ELITE FITNESS
-- MySQL Database Seed Data (Phase 2 Backend)
-- ==============================================================================

USE `dark_matter_gym`;

-- ------------------------------------------------------------------------------
-- 1. Seed: admins
-- Default Superadmin:
-- Username: darkmatter_admin
-- Email: admin@darkmatter-fitness.com
-- Password: DarkMatter#2026!Elite
-- ------------------------------------------------------------------------------
INSERT INTO `admins` (`username`, `email`, `password_hash`, `role`) VALUES
('darkmatter_admin', 'admin@darkmatter-fitness.com', '$2y$12$SQb84zbQlUMjapBSjJlUl.lMRNRQKVcfOFRwTuTfUT.p8GElEMc36', 'superadmin');

-- ------------------------------------------------------------------------------
-- 2. Seed: membership_plans
-- ------------------------------------------------------------------------------
INSERT INTO `membership_plans` (`slug`, `name`, `tier_label`, `tagline`, `monthly_price`, `annual_monthly_price`, `note_monthly`, `note_annual`, `is_popular`, `features`, `sort_order`) VALUES
('initiate', 'INITIATE', 'PROTOCOL 01', 'Essential unrestricted access to the calibrated lifting floor and cardio suite.', 79.00, 63.00, 'Billed monthly • Cancel anytime with 30-day notice', 'Billed annually ($756/yr) • Save $192/yr', 0, 
JSON_ARRAY(
  JSON_OBJECT("name", "Full 24/7 Biometric Gym Access", "included", true),
  JSON_OBJECT("name", "Eleiko Olympic Platforms & Dumbbells", "included", true),
  JSON_OBJECT("name", "Woodway & Concept2 Metabolic Equipment", "included", true),
  JSON_OBJECT("name", "Mobile Locker & Keyless Gate App", "included", true),
  JSON_OBJECT("name", "Infrared Saunas & Cold Plunge Spa", "included", false),
  JSON_OBJECT("name", "Monthly InBody 770 Telemetry Scan", "included", false)
), 1),

('obsidian', 'OBSIDIAN', 'PROTOCOL 02 // MOST SELECTED', 'The ultimate performance standard. Full iron floor and unlimited bio-thermal recovery.', 149.00, 119.00, 'Billed monthly • Most selected protocol', 'Billed annually ($1,428/yr) • Save $360/yr', 1,
JSON_ARRAY(
  JSON_OBJECT("name", "Everything in Initiate Tier", "included", true),
  JSON_OBJECT("name", "Unlimited 38°F Chilled Plunge & Saunas", "included", true),
  JSON_OBJECT("name", "Normatec 3 Dynamic Compression Lounge", "included", true),
  JSON_OBJECT("name", "Bi-Weekly InBody 770 Telemetry Scan", "included", true),
  JSON_OBJECT("name", "2 VIP Guest Passes per Month", "included", true),
  JSON_OBJECT("name", "Dedicated Master Coach Included", "included", false)
), 2),

('black-matter', 'BLACK MATTER', 'PROTOCOL 03 // PRIVATE', 'Private bespoke coaching & executive lounge access. Strictly capped to 75 active seats.', 289.00, 231.00, 'Billed monthly • Strictly limited to 75 active members', 'Billed annually ($2,772/yr) • Save $696/yr', 0,
JSON_ARRAY(
  JSON_OBJECT("name", "Everything in Obsidian Tier", "included", true),
  JSON_OBJECT("name", "4x 1-on-1 Master Coach Sessions / Month", "included", true),
  JSON_OBJECT("name", "Permanent Private Locker with Laundry", "included", true),
  JSON_OBJECT("name", "Bespoke Nutrition & Velocity Profiling", "included", true),
  JSON_OBJECT("name", "Unlimited Guest Privileges", "included", true),
  JSON_OBJECT("name", "Priority Recovery Suite Reservations", "included", true)
), 3);

-- ------------------------------------------------------------------------------
-- 3. Seed: trainers
-- ------------------------------------------------------------------------------
INSERT INTO `trainers` (`slug`, `name`, `role_title`, `specialties`, `category`, `credentials`, `bio`, `image_url`, `sort_order`) VALUES
('marcus-vance', 'MARCUS VANCE', 'HEAD OF MAXIMAL STRENGTH', 'Maximal Strength & Hypertrophy', 'strength',
JSON_ARRAY('CSCS', 'USAW-2', 'IPF ELITE', '12 YRS'),
'Architects progressive overload systems, bar velocity profiling, and myofibrillar hypertrophy. Former competitive powerlifter with 785 lb squat and 835 lb deadlift.',
'https://images.unsplash.com/photo-1567013127542-490d757e51fc?q=80&w=800&auto=format&fit=crop', 1),

('elena-rostova', 'ELENA ROSTOVA', 'DIRECTOR OF METABOLIC SYSTEMS', 'Metabolic Systems & Conditioning', 'metabolic',
JSON_ARRAY('EXOS XPS', 'CROSSFIT L3', 'OLYMPIC DEV', '9 YRS'),
'Specializes in lactate buffering, high-anaerobic threshold power, and metabolic conditioning for hybrid athletes and Hyrox competitors.',
'https://images.unsplash.com/photo-1594381898411-846e7d193883?q=80&w=800&auto=format&fit=crop', 2),

('darius-thorne', 'DARIUS THORNE', 'BIOMECHANICS & COMBAT MOBILITY', 'Biomechanics & Functional Combat', 'combat',
JSON_ARRAY('FRCms', 'FMS-2', 'BOXING MASTER', '14 YRS'),
'Restores compromised joint articulation, develops rotational anti-shear power, and coaches high-speed technical combat striking conditioning.',
'https://images.unsplash.com/photo-1549476464-37392f717541?q=80&w=800&auto=format&fit=crop', 3),

('siobhan-kelly', 'SIOBHAN KELLY', 'OLYMPIC WEIGHTLIFTING LEAD', 'Olympic Weightlifting & Power', 'strength',
JSON_ARRAY('USAW-3', 'CSCS', 'IWF NATL', '10 YRS'),
'Master of the snatch and clean & jerk kinematics. Specializes in triple extension, bar trajectory tracking, and elite vertical force production.',
'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=800&auto=format&fit=crop', 4),

('dr-henrik-lindqvist', 'DR. HENRIK LINDQVIST', 'PHYSIOLOGY & BIOCHEMICAL TELEMETRY', 'Exercise Physiology & Nutrition', 'physiology',
JSON_ARRAY('PHD PHYSIOLOGY', 'CISSN', 'INBODY PRO', '16 YRS'),
'Analyzes autonomic nervous system tone, HRV recovery curves, and clinical body composition telemetry to design personalized nutrition protocols.',
'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?q=80&w=800&auto=format&fit=crop', 5),

('maya-sterling', 'MAYA STERLING', 'CALISTHENICS & RING MASTERY', 'Calisthenics & Ring Mastery', 'combat',
JSON_ARRAY('FRC', 'ELITE GYMNASTICS', 'MOTOR CONTROL', '8 YRS'),
'Teaches straight-arm strength leverage, planche mechanics, gymnastic ring muscle-ups, and active spinal articulation for peak resilience.',
'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=800&auto=format&fit=crop', 6);

-- ------------------------------------------------------------------------------
-- 4. Seed: programs
-- ------------------------------------------------------------------------------
INSERT INTO `programs` (`slug`, `name`, `category`, `badge_text`, `short_desc`, `intensity_score`, `intensity_display`, `duration_minutes`, `burn_calories_range`, `coach_ratio`, `target_adaptation`, `gear_stations`, `recommended_cadence`, `image_url`, `sort_order`) VALUES
('hypertrophy-strength', 'HYPERTROPHY & MAXIMAL STRENGTH', 'strength', 'STRENGTH // HEAVY IRON',
'Periodized linear overload engineered for central nervous system efficiency, mechanical tension, and absolute myofibrillar mass.',
5.0, '5 / 5 [EXTREME LOAD]', 75, '650–850 KCAL', '1 : 6 ATHLETES',
'Myofibrillar hypertrophy, absolute central nervous system recruitment, peak torque output.',
'Eleiko calibrated steel plates, custom calibrated power cages, safety squat bars, dual cable columns.',
'3 to 4 sessions / week',
'https://images.unsplash.com/photo-1574680096145-d05b474e2155?q=80&w=800&auto=format&fit=crop', 1),

('metabolic-conditioning', 'METABOLIC CONDITIONING (METCON)', 'cardio', 'CARDIO // HIGH INTENSITY',
'High-anaerobic power intervals combining curved treadmills, Concept2 SkiErgs, assault bikes, and turf sled acceleration.',
4.5, '4.5 / 5 [HIGH ANAEROBIC]', 50, '750–1000 KCAL', '1 : 8 ATHLETES',
'VO2 max enhancement, lactate threshold buffering, fast-twitch muscle endurance.',
'Woodway Curve treadmills, Concept2 SkiErgs, assault bikes, heavy rogue sleds on 40m indoor turf.',
'2 to 3 sessions / week',
'https://images.unsplash.com/photo-1538805060514-97d9cc17730c?q=80&w=800&auto=format&fit=crop', 2),

('cryo-thermal-recovery', 'THERMAL FLUID & CRYO RECOVERY', 'recovery', 'RECOVERY // BIO-NEURAL',
'Scientific contrast therapy: 38°F chilled immersion baths paired with 195°F Swedish saunas and Normatec lymphatic flushing.',
1.0, '1 / 5 [PASSIVE NEURAL RESET]', 60, '150–250 KCAL', 'SPA TECH ON DUTY',
'Cortisol downregulation, cellular lymphatic drainage, systemic inflammation abatement, HRV elevation.',
'Dual 38°F chilled immersion plunges, 195°F Swedish cedar dry saunas, full-spectrum infrared pods, Normatec 3 compression boots.',
'Post heavy training or dedicated recovery days',
'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=800&auto=format&fit=crop', 3),

('elite-personal-coaching', 'ELITE 1-ON-1 PHYSICAL ARCHITECTURE', 'strength', 'BESPOKE // MASTER COACH',
'Private biometric programming tailored to individual anthropometrics, force-plate velocity profiling, and elite sports prep.',
5.0, 'Dynamic (Tailored 3 to 5 / 5)', 60, '500–900 KCAL', '1 : 1 PRIVATE',
'Individualized biometric periodization, posture/movement screen optimization, tailored competition or longevity prep.',
'Full facility private priority access, InBody 770 body composition analyzer, force plates.',
'Custom prescribed',
'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?q=80&w=800&auto=format&fit=crop', 4),

('combat-conditioning', 'TACTICAL COMBAT & STRIKING', 'functional', 'FUNCTIONAL // KINETIC',
'Heavy bag combinations, rotational kinetic core transfer, anaerobic pacing, and fight-conditioned stamina drill work.',
4.5, '4.5 / 5 [HIGH POWER DENSITY]', 60, '700–950 KCAL', '1 : 6 ATHLETES',
'Rotational kinetic chaining, neuromuscular reactivity, hand-eye spatial cognition, core anti-rotation stability.',
'Fairtex heavy tear-drop bags, elevated octagon ring floor, double-end bags, speed ropes.',
'2 to 3 sessions / week',
'https://images.unsplash.com/photo-1549476464-37392f717541?q=80&w=800&auto=format&fit=crop', 5),

('functional-calisthenics', 'KINETIC MOBILITY & CALISTHENICS', 'functional', 'FUNCTIONAL // CALISTHENICS',
'End-range joint resilience, gymnastic ring leverage, core anti-extension control, and bodyweight power mastery.',
3.5, '3.5 / 5 [HIGH COORDINATION]', 55, '450–600 KCAL', '1 : 8 ATHLETES',
'Joint capsule resilience, gymnastic ring stability, scapular retraction strength, end-range active mobility.',
'Custom steel monkey rigs, wooden gymnastic rings, parallel bars, stall bars, resistance bands.',
'3 sessions / week',
'https://images.unsplash.com/photo-1517963879433-6ad2b056d712?q=80&w=800&auto=format&fit=crop', 6);

-- ------------------------------------------------------------------------------
-- 5. Seed: gallery_images
-- ------------------------------------------------------------------------------
INSERT INTO `gallery_images` (`title`, `category`, `tag_label`, `image_url`, `alt_text`, `sort_order`) VALUES
('Eleiko Olympic Power Platforms', 'iron', 'ARENA 01 // HEAVY IRON', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1000&auto=format&fit=crop', 'Calibrated Olympic Weightlifting Platforms', 1),
('38°F Chilled Plunge & Sauna Suite', 'spa', 'ARENA 03 // BIO-RECOVERY', 'https://images.unsplash.com/photo-1540497077202-7c8a3999166f?q=80&w=1000&auto=format&fit=crop', 'Chilled 38F Plunge Bath and Thermal Saunas', 2),
('40-Meter High-Traction Sled Turf', 'turf', 'ARENA 04 // SPRINT TURF', 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?q=80&w=1000&auto=format&fit=crop', '40m High-Traction Sled Turf', 3),
('Competition Racks & Urethane Dumbbells', 'iron', 'ARENA 01 // HEAVY IRON', 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=1000&auto=format&fit=crop', 'Calibrated Competition Benches and Dumbbell Row', 4),
('Private Biometric Telemetry Lab', 'suites', 'PRIVATE SUITES // 1-ON-1', 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?q=80&w=1000&auto=format&fit=crop', 'Private Master Coach Diagnostic Suite', 5),
('Woodway Curve Metabolic Cinema', 'turf', 'ARENA 02 // CARDIO CINEMA', 'https://images.unsplash.com/photo-1538805060514-97d9cc17730c?q=80&w=1000&auto=format&fit=crop', 'Woodway Curve Sprint Corridor', 6),
('195°F Swedish Cedar Thermal Sauna', 'spa', 'ARENA 03 // THERMAL', 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?q=80&w=1000&auto=format&fit=crop', 'Swedish Cedar Infrared Sauna', 7),
('Obsidian Executive Locker Suites', 'suites', 'PRIVATE SUITES // EXECUTIVE', 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=1000&auto=format&fit=crop', 'Obsidian Member Executive Locker Suites', 8);

-- ------------------------------------------------------------------------------
-- 6. Seed: site_settings
-- ------------------------------------------------------------------------------
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_name', 'DARK MATTER — Elite Fitness', 'Official brand brand title'),
('site_tagline', 'Redefine Your Human Threshold', 'Brand headline manifesto'),
('contact_email', 'concierge@darkmatter-fitness.com', 'Primary concierge email address'),
('contact_phone', '+1 (555) 019-8234', 'Primary front desk contact number'),
('address', '400 Obsidian Boulevard, District 7, Metropolis, NY 10001', 'Physical flagship address'),
('geo_coordinates', '40.7128° N, 74.0060° W', 'Flagship GPS telemetry coordinates'),
('whatsapp_number', '15550198234', 'Direct encrypted concierge WhatsApp number'),
('operating_hours_members', '24/7/365 Unrestricted Biometric Access', 'Member entry schedule'),
('operating_hours_staff', '06:00 – 22:00 Daily', 'Staffed front desk hours'),
('active_member_cap', '500', 'Maximum active members to eliminate overcrowding'),
('current_active_members', '462', 'Current active registered members'),
('facility_sq_ft', '45,000', 'Total square footage of training floor');

-- ------------------------------------------------------------------------------
-- 7. Seed: members, member_payments, member_attendance
-- ------------------------------------------------------------------------------
INSERT INTO `members` (`membership_id`, `full_name`, `email`, `phone`, `plan_id`, `plan_name`, `billing_cycle`, `start_date`, `expiry_date`, `status`, `emergency_contact`, `notes`) VALUES
('DM-2026-0101', 'Alexander Vance', 'a.vance@vanceholdings.com', '+1 (555) 301-4491', 3, 'BLACK MATTER', 'annual', '2026-01-15', '2027-01-15', 'active', 'Elena Vance (+1 555-301-9988)', 'Executive athlete. Requires biometric locker 14 and post-workout sauna suite reservation.'),
('DM-2026-0102', 'Maya Lin Thorne', 'maya.thorne@metrolabs.io', '+1 (555) 782-3901', 2, 'OBSIDIAN', 'monthly', '2026-03-01', '2026-04-01', 'active', 'David Thorne (+1 555-782-3902)', 'Training for hybrid powerlifting competition. Uses 38F cold plunge daily.'),
('DM-2026-0103', 'Marcus Sterling', 'sterling.marcus@apexcapital.com', '+1 (555) 234-8877', 2, 'OBSIDIAN', 'monthly', '2026-02-15', '2026-04-15', 'active', 'Sarah Sterling (+1 555-234-8899)', 'Assigned to Coach Viktor Stone for barbell strength progressions.'),
('DM-2026-0104', 'Dr. Elena Rostova', 'elena.rostova@neurolink.org', '+1 (555) 891-2245', 1, 'INITIATE', 'monthly', '2026-01-10', '2026-02-10', 'expired', 'Boris Rostov (+1 555-891-2200)', 'Renewal reminder dispatched. Expressed interest in upgrading to Obsidian tier.'),
('DM-2026-0105', 'Gabriel Cross', 'g.cross@crossathletics.com', '+1 (555) 412-9018', 3, 'BLACK MATTER', 'monthly', '2026-04-05', '2026-05-05', 'pending', 'Rachel Cross (+1 555-412-9019)', 'Online application completed. Biometric facial scan scheduled at concierge desk.');

INSERT INTO `member_payments` (`member_id`, `transaction_id`, `amount`, `payment_method`, `payment_status`, `payment_date`, `notes`) VALUES
(1, 'TXN-DM-20260115-0981', 2750.00, 'Credit Card', 'completed', '2026-01-15', 'Annual Black Matter enrollment privilege'),
(2, 'TXN-DM-20260312-5541', 149.00, 'UPI', 'completed', '2026-03-01', 'Monthly Obsidian protocol renewal'),
(3, 'TXN-DM-20260320-1129', 149.00, 'Credit Card', 'completed', '2026-02-15', 'Monthly Obsidian protocol enrollment'),
(4, 'TXN-DM-20260120-7712', 79.00, 'Debit Card', 'completed', '2026-01-10', 'Monthly Initiate protocol enrollment'),
(5, 'TXN-DM-20260401-4409', 289.00, 'UPI', 'completed', '2026-04-01', 'Provisional online Black Matter checkout');

INSERT INTO `member_attendance` (`member_id`, `check_in_time`, `check_out_time`, `method`, `notes`) VALUES
(1, NOW() - INTERVAL 2 HOUR, NULL, 'Biometric Facial Gate', 'Currently on training floor [Zone A / Iron Pit]'),
(2, NOW() - INTERVAL 3 HOUR, NOW() - INTERVAL 1 HOUR, 'Biometric Facial Gate', 'Completed 75-min protocol & Cold Plunge'),
(3, NOW() - INTERVAL 5 HOUR, NOW() - INTERVAL 3 HOUR, 'Biometric Facial Gate', 'Master coach 1-on-1 session completed');

