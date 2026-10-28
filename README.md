# 24HRS FITNESS — 24/7 Premium Gym & Training Club

> **"TRAIN HARDER. LIVE BETTER."**  
> A high-performance, modern gym management platform and public website. Featuring a luxury obsidian-black and electric-neon-cyan theme matching the brand logo, a secure PHP 8.2+ backend architecture, a relational MySQL 8+ database (`fitness_gym`), an interactive online membership booking system, and a comprehensive **Admin Management Panel**.

---

## 1. Project Overview & Brand Identity

* **Brand Name**: 24HRS FITNESS
* **Tagline**: Train Harder. Live Better. (Open 24 Hours // 7 Days a Week)
* **Design Theme**: Obsidian X Luxury Theme
  * **Primary Background**: `#06080B` (Deep Obsidian Black)
  * **Secondary Background**: `#0E1112` (Card and Section Charcoal)
  * **Accent Color**: `#00E5FF` (Electric Neon Cyan — matching official brand logo)
  * **Metallic Detail**: `#D1D5D4` / `#8F9493` (Subtle borders and secondary text)
  * **Primary Text**: `#F5F5F5` (High-contrast crisp white)
* **Typography**:
  * Headings: **`Barlow Condensed`** (Bold athletic weights 700, 800, 900)
  * Body Text: **`Plus Jakarta Sans`** (Clean, legible weights 400, 500, 600)

---

## 2. Key Features

### 🌟 Public Website
1. **Homepage (`index.php`)**:
   - Hero section with cinematic fitness imagery, direct CTA buttons (`View Membership Plans`, `Get Free 1-Day Pass`, `Book Gym Tour`).
   - Key stats counter (25,000 Sq Ft floor, 100+ modern machines, 98% satisfaction, 24/7 access).
   - About us showcase and core pillars.
   - Workout programs preview with direct inspection modal.
   - Gym advantage comparison (heavy weights, body composition scans, steam & sauna, 24/7 keycard access).
   - Certified coach roster with consultation booking.
   - Membership pricing cards with monthly/annual billing toggle and Rupee (`₹`) pricing.
   - Genuine member reviews & ratings.
2. **Workout Programs (`programs.php`)**:
   - Filterable program tabs: **Strength & Muscle**, **Cardio & Fat Loss**, **Steam & Sauna Recovery**, **CrossFit & Boxing**.
   - Gym facilities & zones breakdown.
   - Weekly group workout class timetable (Morning, Afternoon, Evening slots).
   - Interactive Program Details modal.
3. **Membership Plans (`membership.php`)**:
   - 3 dynamic plans: **Starter** (₹1,500/mo), **Pro Fitness** (₹2,500/mo), **Elite VIP** (₹4,500/mo).
   - Monthly / Annual toggle (20% discount calculation).
   - Detailed side-by-side feature comparison table.
   - Common gym FAQ accordion.
4. **Personal Trainers (`trainers.php`)**:
   - Certified personal coaches roster with disciplines and bios.
   - Filter by specialty (Strength, Fat Loss, CrossFit, Mobility).
   - Direct "Book Free Consultation" modal.
5. **About & Gallery (`about.php`)**:
   - Gym history, philosophy, and equipment standards.
   - Categorized facility photo gallery (Weights, Steam & Sauna, Turf, Cardio) with full-screen lightbox.
6. **Contact & Location (`contact.php`)**:
   - Operating hours, email, physical gym address with interactive Google Maps link.
   - Direct **Call Now** (`+91 99201 20785`) and **WhatsApp Chat** buttons.
   - Multi-mode inquiry form: Free 1-Day Trial Pass, Book Gym Tour, General Inquiry.

---

### 🛡️ Online Membership & Booking System
* **Free 1-Day Pass Modal**: Quick trial pass generation with instant confirmation.
* **Gym Tour Booking**: Schedule in-person tour with preferred date and time slot.
* **Trainer Consultation Booking**: Direct session request with selected coach.
* **Online Membership Checkout (`/api/membership_apply.php`)**:
  - Plan selection with live price calculation.
  - Customer personal details & joining date.
  - Flexible payment options:
    - **Pay at Gym Reception** (Cash / UPI / Card on arrival)
    - **Pay Online Now** (UPI / Credit & Debit Card / NetBanking)
  - Instant booking confirmation modal with reference code (e.g. `MBR-2026-XXXX`).

---

### ⚡ Admin Management Panel (`/admin`)
Accessible via `http://localhost/Fitness%20gym/admin/` with secure session authentication:

1. **Dashboard Overview (`admin/index.php`)**:
   - Real-time KPI summary: Total Enquiries, Total Members, Active Members, and Revenue (in ₹).
   - Recent customer enquiries and member applications feeds.
   - Quick-action shortcuts.
2. **Members Management (`admin/members.php`)**:
   - Member profiles with auto-generated Membership IDs.
   - Membership plan details, joining date, and expiry date.
   - Live status badges: **Active**, **Expired**, **Pending**.
   - Payment records & fee collection status.
3. **Membership Plans (`admin/plans.php`)**:
   - Full CRUD: Add, edit, toggle active status, and delete plans.
   - Monthly and annual pricing in INR (`₹`), feature list editor.
   - Deletion safety preventing removal of plans with active members.
4. **Trainer Team (`admin/trainers.php`)**:
   - Full CRUD: Add coach profiles, upload photos, specify certifications, specialties, and social handles.
5. **Workout Programs (`admin/programs.php`)**:
   - Full CRUD: Add programs, set target categories, intensity ratings, durations, and weekly schedules.
6. **Gym Gallery (`admin/gallery.php`)**:
   - Full CRUD: Upload facility photos, organize by zone category, edit descriptions, toggle display.
7. **Customer Enquiries (`admin/enquiries.php`)**:
   - View Free 1-Day Pass leads and Gym Tour requests.
   - Search by name, email, or phone.
   - Filter by status (`pending`, `contacted`, `completed`, `cancelled`).
   - One-click status updates with CSRF security.
8. **Member Applications (`admin/applications.php`)**:
   - Review online membership joining requests.
   - View selected payment mode (Reception vs Online).
   - Approve / Activate memberships with instant status updates.
9. **Website Settings (`admin/settings.php`)**:
   - Update gym contact phone, official email, address, and Google Maps coordinates.
   - Update WhatsApp concierge number and social media links.
   - Configure gym opening hours.

---

## 3. Technology Stack & Security

| Layer | Technology | Details |
|---|---|---|
| **Backend** | PHP 8.2+ / 8.3 | Object-oriented PDO, modular components, strict typing |
| **Database** | MySQL 8.0+ | Relational schema (`fitness_gym`), foreign keys, indexed lookups |
| **Frontend** | HTML5, CSS3, ES6+ JS | Vanilla JavaScript (no heavy frameworks), CSS variables, responsive design |
| **Server** | Apache (XAMPP / Docker) | Clean URL rewrites (`.htaccess`), security headers, Gzip compression |
| **Security** | CSRF, Session Hardening | `session_regenerate_id()`, prepared statements (Zero SQLi), honeypots |
| **File Uploads** | PHP FileInfo MIME Guard | Strict MIME check (`jpeg`, `png`, `webp`), execution disabled in `uploads/` |

---

## 4. Local Setup Instructions (XAMPP on Windows)

### Prerequisites
* [XAMPP](https://www.apachefriends.org/) installed with **Apache** and **MySQL**.
* Web root directory: `C:\xampp\htdocs\Fitness gym`

### Step 1: Start Apache and MySQL
Open the **XAMPP Control Panel** and click **Start** next to:
- **Apache** (Port 80 / 443)
- **MySQL** (Port 3306)

### Step 2: Set Up Database (`fitness_gym`)
1. Open your browser and navigate to **phpMyAdmin**: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Create a new database named:
   ```sql
   fitness_gym
   ```
3. Click on the newly created `fitness_gym` database, go to the **Import** tab, and select:
   ```
   C:\xampp\htdocs\Fitness gym\database\fitness_gym.sql
   ```
4. Click **Import** (or **Go**) at the bottom. All 11 tables and clean initial data will be loaded automatically.

*(Alternatively, run via MySQL CLI:)*
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS fitness_gym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p fitness_gym < "database/fitness_gym.sql"
```

### Step 3: Configure Environment File (`.env`)
The `.env` file in the project root is pre-configured for local XAMPP:
```ini
APP_ENV=development
APP_DEBUG=false
APP_NAME="24HRS FITNESS — 24/7 Premium Gym"
APP_URL=http://localhost/Fitness%20gym

# Database Credentials
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=fitness_gym
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# Security Tokens
SESSION_SECRET=e7b8f94c1a2d3e4f5a6b7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f
RATE_LIMIT_PER_MINUTE=10
RATE_LIMIT_WINDOW_SECONDS=300
HONEYPOT_FIELD=dm_telemetry_check
```

### Step 4: Access the Website & Admin Panel

* 🌐 **Public Website**:
  [http://localhost/Fitness%20gym/](http://localhost/Fitness%20gym/)

* 🔐 **Admin Management Panel**:
  [http://localhost/Fitness%20gym/admin/](http://localhost/Fitness%20gym/admin/)

  **Default Admin Credentials**:
  * **Email**: `fitness_gym@gmail.com`
  * **Password**: `fitness123`

---

## 5. Project Directory Structure

```
c:\xampp\htdocs\Fitness gym\
│
├── .env                              # Environment credentials & app configuration (ignored in Git)
├── .env.example                      # Safe environment template without secrets
├── .gitignore                        # Git exclusion rules (secrets, OS files, uploads, logs)
├── .htaccess                         # Apache URL routing, security headers & Gzip compression
├── README.md                         # Full project documentation & execution guide
│
├── index.php                         # Home page (Dynamic programs, trainers, plans from MySQL)
├── programs.php                      # Workout programs & gym zones (Filter tabs & schedule timetable)
├── membership.php                    # Membership plans & pricing (Monthly/annual toggle, apply modal)
├── trainers.php                      # Personal coaches roster (Specialty filter, consultation booking)
├── about.php                         # Gym story & facility gallery (Dynamic photos with lightbox)
├── contact.php                       # Contact info, Google Maps, Call/WhatsApp, 1-Day Pass form
├── 404.php                           # Custom branded 404 error page
├── 500.php                           # Custom branded 500 server error page
├── healthz.php                       # Healthcheck endpoint
│
├── admin/
│   ├── index.php                     # Admin Dashboard (KPI stats, revenue counter, recent activity)
│   ├── login.php                     # Secure Admin Login (CSRF, brute-force rate limiting)
│   ├── logout.php                    # Secure Admin Logout (Session invalidation & cookie cleanup)
│   ├── members.php                   # Member Management (Profiles, membership IDs, active/expired status)
│   ├── plans.php                     # Membership Plans CRUD (Add, edit, toggle, delete plans in ₹)
│   ├── trainers.php                  # Trainer Team CRUD (Add, edit, upload photo, specialty tags)
│   ├── programs.php                  # Workout Programs CRUD (Categories, intensity, duration, schedule)
│   ├── gallery.php                   # Gym Gallery CRUD (Upload facility photos, categories, status)
│   ├── enquiries.php                 # Customer Enquiries (1-Day pass leads, tour requests, status update)
│   ├── applications.php              # Membership Applications (Online join requests, payment mode review)
│   ├── settings.php                  # Gym Settings (Phone, email, address, WhatsApp, social links)
│   └── includes/
│       ├── auth.php                  # Session authentication guard & flash messaging
│       ├── header.php                # Admin header, navigation sidebar, dynamic badge counters
│       ├── footer.php                # Admin footer, confirmation modals
│       └── uploader.php              # Secure image uploader helper with MIME verification
│
├── api/
│   ├── csrf_token.php                # Endpoint providing active session CSRF token
│   ├── enquiry.php                   # Endpoint for 1-Day pass, gym tours, and general inquiries
│   └── membership_apply.php          # Endpoint for online membership checkout & applications
│
├── includes/
│   ├── Database.php                  # PDO singleton with prepared statements & error handling
│   ├── Env.php                       # Lightweight .env parser
│   ├── Security.php                  # CSRF protection, session hardening, honeypot, rate limiting
│   ├── helpers.php                   # Global XSS escaping helper function e()
│   ├── header.php                    # Shared public header with navigation and CSRF tokens
│   ├── footer.php                    # Shared public footer with WhatsApp link and admin portal button
│   └── modals.php                    # Shared modal dialogs (Pass, tour, join checkout, consultation)
│
├── database/
│   └── fitness_gym.sql               # Clean MySQL database schema and seed data
│
├── css/
│   ├── style.css                     # Design tokens, typography, navbar, animations
│   ├── components.css                # Cards, badges, buttons, modals, pricing tables
│   └── admin.css                     # Dedicated responsive admin dashboard stylesheet
│
├── js/
│   ├── main.js                       # Navbar toggle, scroll effects, toast notifications
│   ├── membership.js                 # Monthly/annual billing calculations, plan selection
│   ├── programs.js                   # Category filtering & program inspection modal
│   ├── gallery.js                    # Photo category filters & image lightbox
│   └── forms.js                      # AJAX form submissions, CSRF injection, feedback banners
│
├── assets/
│   └── images/                       # Brand logo, icons, and hero photography
│
├── uploads/                          # User-uploaded images (protected by .htaccess)
│   ├── .htaccess                     # Script execution prevention rule
│   ├── gallery/                      # Uploaded gallery images
│   └── trainers/                     # Uploaded trainer headshots
│
└── scripts/
    ├── backup.php                    # Automated MySQL database backup utility
    ├── restore.php                   # Database restore utility from backup archive
    └── create_admin.php              # Safe CLI script to create or reset admin credentials
```

---

## 6. Maintenance & Backup Commands

### Create a Database Backup
Run the backup script via terminal or Command Prompt:
```bash
php scripts/backup.php
```
*Creates a timestamped compressed backup in the `backups/` folder.*

### Restore a Database Backup
```bash
php scripts/restore.php backups/darkmatter_backup_YYYY-MM-DD_HHMMSS.sql.gz
```

### Create a New Admin User via CLI
```bash
php scripts/create_admin.php username email@example.com "YourPasswordHere" superadmin
```

---

## 7. License & Credits

* **Project**: 24HRS FITNESS
* **Engineered for**: High-Performance Fitness Centers & Gym Owners
* **Theme**: Obsidian X Luxury Theme
