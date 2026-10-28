# DARK MATTER — Elite Fitness (Phase 3 Full Stack Platform & Admin Nexus)

> **"Redefine Your Human Threshold"**  
> A futuristic, high-performance physical architecture gym platform. Engineered with an uncompromising black and electric lime aesthetic, metallic surfaces, high-contrast athletic imagery, secure PHP 8.3+ backend architecture, a relational MySQL 8+ database, and a fully functional, hardened **Admin Command Dashboard (Telemetry Nexus)**.

---

## 1. Design System & Brand Identity (Preserved Across Public & Admin)

- **Main Background**: `#080A0A`
- **Secondary Surfaces**: `#111414`
- **Elevated Card Surfaces**: `#131617` / `#161A1C` / `#1F2427`
- **Electric Lime Accent**: `#B8FF2C` (Buttons, active badges, neon indicators, focus states)
- **Metallic Silver**: `#BFC3C7` / `#E2E4E6` (Borders, metadata, secondary headers)
- **Crisp White**: `#F5F5F5` (High-contrast typography)
- **Controlled Glow**: `0 0 20px rgba(184, 255, 44, 0.28)`
- **Typography**:
  - Headings: **`Barlow Condensed`** (Bold athletic weights 700, 800, 900)
  - Body Copy: **`Plus Jakarta Sans`** (Weights 400, 500, 600, 700)
  - Technical / Telemetry tags: **`Space Mono`** (Weight 400, 700)
- **Aesthetic**:
  - Predominantly black, cinematic contrast, angled chamfered badges, metallic borders, zero generic rounded cards or distracting animations.

---

## 2. Phase 3 Admin Dashboard Architecture (`/admin`)

The Admin Nexus provides real-time facility management, athlete leads processing, and dynamic database controls:

1. **Authentication & Session Security (`admin/includes/auth.php`)**:
   - Session-based authentication with `session_regenerate_id(true)` upon successful verification.
   - Brute-force rate limiting: throttles IP addresses exceeding 5 failed login attempts per 15 minutes.
   - Global Route Guard (`Auth::requireAuth()`) enforcing authentication on all dashboard routes.
   - Secure logout destroying session cookies, session variables, and memory states.
2. **Dashboard Overview (`admin/index.php`)**:
   - Real-time telemetry counters (total enquiries, pending leads, applications, active protocols, master coaches, gallery assets).
   - Recent activity feed showing latest 5 guest transmissions and latest 5 membership applications.
   - Direct database quick-action toolbar.
3. **Membership Plan Management (`admin/plans.php`)**:
   - Full CRUD: Create, Edit, Activate/Deactivate toggle, and Delete with modal confirmation.
   - Dynamic JSON feature list builder (newline-separated text to JSON array).
   - Foreign-key protection preventing accidental deletion of plans with existing athlete applications.
4. **Master Coach Management (`admin/trainers.php`)**:
   - Full CRUD: Create, Edit, Publish/Unpublish toggle, and Delete with modal confirmation.
   - Supports verified image uploads or existing image URLs.
   - Dynamic credentials list editor and discipline categorization.
5. **Training Protocol Management (`admin/programs.php`)**:
   - Full CRUD: Create, Edit, Publish/Unpublish toggle, and Delete with modal confirmation.
   - Intensity score tracking (1.0–5.0), burn ranges, coach ratios, target adaptations, and equipment loadouts.
6. **Spatial Gallery Management (`admin/gallery.php`)**:
   - Full CRUD: Image uploads or URLs, category filtering, taglines, active toggle, and deletion.
   - Secure disk deletion for locally stored image files.
7. **Guest Enquiries & Leads Management (`admin/enquiries.php`)**:
   - Search by athlete name, email, phone, or keyword.
   - Filter by status (`pending`, `verified`, `scheduled`, `completed`, `cancelled`) or inquiry type.
   - Full modal inspection with client IP telemetry, user agent, date/slot preference, and message.
   - In-line status update dropdown with instant CSRF verification.
   - Pagination (15 records per page).
8. **Membership Applications Management (`admin/applications.php`)**:
   - Search by athlete legal name, email, phone, or protocol.
   - Filter by status (`pending`, `verified`, `active`, `rejected`) and plan.
   - Modal inspection showing billing commitment, monthly rate snapshot, and facility location.
   - In-line status update dropdown with instant CSRF verification.
   - Pagination (15 records per page).
9. **Facility Settings & Telemetry (`admin/settings.php`)**:
   - Gym platform name, headline manifesto, member caps, and active count.
   - Concierge front-line telephone, secure email, physical address, and GPS coordinates.
   - Direct encrypted WhatsApp concierge line.
   - Operating hours (biometric 24/7 access vs. staffed reception).
   - Social channels (Instagram, X / Twitter, YouTube).
10. **Hardened File Upload Security (`admin/includes/uploader.php` & `uploads/`)**:
    - Strict MIME-type inspection via PHP `FileInfo` (`image/jpeg`, `image/png`, `image/webp`, `image/gif`).
    - Extension whitelisting and 5MB size ceiling constraint.
    - Safe random filename generation (`bin2hex(random_bytes(16)) . '.' . $ext`).
    - Dedicated `uploads/.htaccess` prohibiting Apache script execution (`php_flag engine off`, `Require all denied` for executables).

---

## 3. Complete Folder & File Structure

```
c:\xampp\htdocs\Fitness gym\
│
├── .env                              # Local environment credentials & app settings
├── .env.example                      # Safe environment template without secrets
├── README.md                         # Project documentation, execution guide & test matrix
│
├── index.php                         # Home page (Dynamic programs, trainers, and plans from MySQL)
├── membership.php                    # Membership plans (Dynamic plans, pricing calculations, apply modal)
├── programs.php                      # Protocols page (Dynamic training protocols, inspection modal)
├── trainers.php                      # Coaches page (Dynamic coach roster, specialty filters)
├── about.php                         # Brand & spatial architecture (Dynamic facility gallery from DB)
├── contact.php                       # Contact & join (Dynamic settings, multi-mode inquiry form)
│
├── admin/
│   ├── index.php                     # Dashboard Overview (Live telemetry, stats, recent activity)
│   ├── login.php                     # Secure Admin Authentication Portal (CSRF, brute-force limits)
│   ├── logout.php                    # Secure Logout Handler (Session invalidation, cookie destruction)
│   ├── plans.php                     # Membership Plans Management (CRUD, toggle, delete confirmation)
│   ├── trainers.php                  # Master Coaches Management (CRUD, headshot upload, publish toggle)
│   ├── programs.php                  # Training Protocols Management (CRUD, intensity specs, upload)
│   ├── gallery.php                   # Facility Gallery Management (CRUD, secure upload, active toggle)
│   ├── enquiries.php                 # Guest Enquiries Management (Search, filter, modal inspect, status update)
│   ├── applications.php              # Membership Applications Management (Search, filter, status update)
│   ├── settings.php                  # Facility & Telemetry Settings (Contact, address, hours, socials)
│   └── includes/
│       ├── auth.php                  # Session authentication guard & flash messaging
│       ├── header.php                # Admin layout header, navigation sidebar, dynamic badge counters
│       ├── footer.php                # Admin layout footer, global confirmation modal, mobile navigation
│       └── uploader.php              # Secure image uploader helper with MIME verification
│
├── api/
│   ├── csrf_token.php                # GET endpoint providing active session CSRF token
│   ├── enquiry.php                   # POST endpoint for VIP passes, tours, inquiries, consultations
│   └── membership_apply.php          # POST endpoint validating plan slug, calculating totals & inserting application
│
├── includes/
│   ├── Env.php                       # Lightweight .env parser with type-casting
│   ├── Database.php                  # PDO singleton with prepared statements & credential-safe error isolation
│   ├── Security.php                  # Session hardening, CSRF verification, honeypots, rate limiter, sanitizers
│   ├── helpers.php                   # Global XSS escaping helper function e()
│   ├── header.php                    # Shared public header component with active route detection & CSRF
│   ├── footer.php                    # Shared public footer component with admin portal access link
│   └── modals.php                    # Dynamic modal component (VIP passes, consultations, membership apply)
│
├── uploads/
│   ├── .htaccess                     # Security rule disabling script execution inside uploads
│   ├── index.html                    # Directory browsing prevention
│   ├── gallery/                      # Uploaded gallery images
│   └── trainers/                     # Uploaded coach headshots
│
├── scripts/
│   └── create_admin.php              # Safe CLI utility to generate / reset admin credentials
│
├── database/
│   ├── dark_matter.sql               # Unified DDL schema and complete demo seed data
│   ├── schema.sql                    # Clean DDL table definitions with foreign keys and indexes
│   └── seed.sql                      # Athletic seed records (plans, coaches, protocols, gallery, settings)
│
├── css/
│   ├── style.css                     # Public design tokens, typography, layout, sticky navbar, mobile drawer
│   ├── components.css                # Cards, badges, buttons, modals, toasts, pricing tables, form inputs
│   └── admin.css                     # Dedicated responsive admin dashboard stylesheet
│
├── js/
│   ├── main.js                       # Public navigation, sticky header, stat counters, toasts
│   ├── membership.js                 # Monthly/annual billing calculation, dynamic database plan checkout
│   ├── programs.js                   # Interactive category filters & dynamic protocol inspection modal
│   ├── gallery.js                    # Filterable facility gallery with full-screen image lightbox
│   └── forms.js                      # Asynchronous AJAX fetch(), CSRF injection, honeypot, DB feedback banners
│
└── assets/
    └── images/
        ├── logo.svg                  # Brand geometric diamond emblem & typography
        └── favicon.svg               # SVG brand favicon
```

---

## 4. Local Setup & Admin Provisioning Instructions

### Step 1: Configure Environment Variables
Copy `.env.example` to `.env` in the project root:
```ini
APP_ENV=local
APP_DEBUG=false
APP_URL=http://localhost/Fitness%20gym

# Database Credentials
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=dark_matter_gym
DB_USER=root
DB_PASS=root
DB_CHARSET=utf8mb4
```

### Step 2: Import MySQL Database
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS dark_matter_gym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p dark_matter_gym < database/dark_matter.sql
```

### Step 3: Provision or Reset Admin Account via CLI
Use the secure administrative CLI tool:
```bash
php scripts/create_admin.php <username> <email> <password> [role]
```
*Example:*
```bash
php scripts/create_admin.php darkmatter_admin admin@darkmatter-fitness.com "DarkMatter#2026!Elite" superadmin
```
*(You can also run `php scripts/create_admin.php` without arguments to enter credentials through interactive CLI prompts.)*

### Step 4: Access Application
1. **Public Website**: Visit `http://localhost/Fitness%20gym/` (or `http://127.0.0.1:8000/`)
2. **Admin Command Nexus**: Visit `http://localhost/Fitness%20gym/admin/login.php` (or click "Nexus Admin" in the website footer)

---

## 5. Verified Feature Checklist (Phase 3 Test Results)

Automated test suites (`scratch/admin_test_suite.php` and `scratch/http_test.php`) executed **47 tests** with **0 failures**:

- [x] **Route Guard Protection**: Unauthenticated requests to all 8 admin routes (`index.php`, `plans.php`, `trainers.php`, `programs.php`, `gallery.php`, `enquiries.php`, `applications.php`, `settings.php`) are blocked and redirected to `login.php`.
- [x] **Session Authentication**:
  - Invalid passkey rejected with alert banner.
  - Missing CSRF token halted.
  - Successful authentication regenerates session ID via `session_regenerate_id(true)` to prevent fixation attacks.
- [x] **Dashboard Telemetry**: Displays real-time counts for guest transmissions, applications, protocols, and coaches, with fast-action shortcuts and recent activity feeds.
- [x] **Membership Plan CRUD**:
  - Created experimental plan `TITAN PROTOCOL` via POST with CSRF.
  - Modified prices and feature checklists; verified in MySQL.
  - Toggled active status between published and unpublished.
  - Safely verified foreign-key constraints before deletion.
- [x] **Master Coach CRUD**:
  - Enlisted coach profile with image upload and credentials.
  - Updated role and discipline specialties.
  - Toggled publish status; deleted with confirmation.
- [x] **Training Protocol CRUD**:
  - Forged protocol with intensity ratings (4.9/5.0), target adaptations, and equipment loadouts.
  - Verified persistence in MySQL and deleted with confirmation.
- [x] **Spatial Gallery Management**:
  - Added facility assets with MIME-checked file upload and safe URL validation.
  - Verified disk cleanup on deletion.
- [x] **Guest Enquiries Management**:
  - Searched by athlete name and verified instant query filtering.
  - Filtered by status (`pending`, `verified`, `scheduled`, etc.).
  - Inspected full message, IP address, and user agent telemetry via modal.
  - Updated status in real-time with CSRF token.
- [x] **Membership Applications Management**:
  - Searched and filtered by protocol and status.
  - Inspected athlete rate snapshot and billing commitment.
  - Updated application status in MySQL.
- [x] **Facility Settings Management**:
  - Updated telephone, email, physical coordinates, and operating hours; verified in `site_settings`.
- [x] **Secure Logout**: Terminated session, cleared session cookies, and confirmed subsequent access is revoked.

---

## 6. Production Deployment Guide (PHP 8.3 + MySQL 8+)

### Architecture Overview
- **Web Runtime**: Docker Container (`php:8.3-apache-bookworm`) with Apache mod_rewrite, headers, deflate, expires, and opcache.
- **Hosting Platform**: **Render** (using Docker runtime via `render.yaml`) or any container-based cloud (AWS ECS, Google Cloud Run, DigitalOcean App Platform).
- **Persistent Database**: Dedicated external MySQL 8.0+ service (**Aiven for MySQL**, PlanetScale, or Railway MySQL) with mandatory TLS/SSL encryption. *SQLite and JSON databases are strictly prohibited.*
- **Persistent Uploads**: Render Persistent Disk mounted at `/var/www/html/uploads` or S3/R2-compatible object storage via native SigV4 (`STORAGE_DRIVER=s3`).

### Local Production Emulation via Docker Compose
To run a complete production-identical containerized stack locally:
```bash
# Build and run web + MySQL 8 in background
docker compose up -d --build

# View container logs
docker compose logs -f

# Verify zero-secret healthcheck
curl -i http://localhost:8080/healthz.php

# Stop stack
docker compose down
```

### Automated Database Migrations & Initial Admin Provisioning
The container entrypoint (`docker-entrypoint.sh`) runs `scripts/migrate.php` on startup:
```bash
# Run migrations manually on CLI:
php scripts/migrate.php

# Or with automatic admin provisioning:
ADMIN_INIT_USERNAME=darkmatter_admin \
ADMIN_INIT_EMAIL=admin@darkmatter-fitness.com \
ADMIN_INIT_PASSWORD="YourSecurePasswordHere" \
php scripts/migrate.php
```

### Database Backup & Restore Utilities
Automated database backup script produces compressed `.sql.gz` archives:
```bash
# Create compressed backup:
php scripts/backup.php

# Restore from backup archive:
php scripts/restore.php backups/darkmatter_backup_YYYY-MM-DD_HHMMSS.sql.gz
```

### Production Verification Test Suite
Run the 38-assertion production readiness test suite:
```bash
php scratch/prod_verification_test.php
# Result: 38 PASSED, 0 FAILED (100% Pass Rate)
```
