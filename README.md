<div align="center">

# 🏥 Care Plus — Digital Health & Hospital Management System

**A secure, modern, and lightweight Hospital Management System & Diagnostic Health Report Generator built with PHP, MySQL, and Vanilla Web Technologies.**

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Security](https://img.shields.io/badge/Security-PDO%20%7C%20CSRF%20%7C%20Bcrypt-2ea44f?style=for-the-badge&logo=shield)](https://github.com/)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

[Features](#-key-features) • [Demo Logins](#-demo-credentials) • [Installation](#-installation--quick-start) • [Security](#-security-architecture) • [Directory Structure](#-directory-structure)

---

</div>

## 📌 Overview

**Care Plus** is an end-to-end hospital management and clinical reporting web application. Designed for clinics and diagnostic centers, it streamlines patient registration, automated clinical report calculation, appointment scheduling with doctor time-slot allocation, and role-based portal access for administrators and patients.

Everything is self-contained and ready to run with zero external npm/composer dependencies — drop it into your local Apache/MySQL server (XAMPP, WAMP, MAMP) or PHP built-in server.

---

## ✨ Key Features

### 👨‍⚕️ 1. Doctor & Patient Administration
- **Patient Records:** Add, edit, search, and manage patient profiles with full medical history.
- **Doctor Directory:** Manage attending physicians, specializations, contact details, and clinic hours.
- **Admin Dashboard:** High-level metrics for patient intake, reports generated, active doctors, and recent activity logs.

### 🧪 2. Automated Diagnostic Report Generator
- **10-Test Diagnostic Panel:** Covers key blood, metabolic, and clinical parameters.
- **Automatic Status Calculation:** Instantly evaluates test inputs against reference ranges to flag values as **Normal**, **Low**, or **High**.
- **Configurable Medical References:** Adjust healthy ranges and units directly from the admin panel without modifying code.
- **Print & PDF Export:** Clean, paper-ready medical reports formatted with hospital headers, doctor signatures, and automated findings using print-optimized CSS.

### 📅 3. "Book My Care" Smart Appointment Booking
- **Public Walk-in Booking:** Patients can book visits online without needing prior registration.
- **Intelligent Slot Engine:** Automatically allocates 15-minute non-overlapping slots (9:00 AM–12:00 PM and 2:00 PM–7:00 PM; Sundays closed).
- **Booking Slips:** Generates a printable appointment slip with an appointment code and report-to instructions.
- **Session-Based Appointment Tracking:** Visitors can view or cancel bookings made in their session.
- **Admin Booking Analytics:** Monitor bookings across any chosen date and analyze 24-hour booking volume.

### 👤 4. Patient Self-Service Portal
- **Secure Access:** Dedicated login portal for patients to view their medical profile.
- **Diagnostic History:** Browse past diagnostic reports, download digital copies, and track health trends over time.

---

## 🔐 Security Architecture

- **Role-Based Access Control (RBAC):** Strict separation between `admins` and `users` authenticated via `require_admin()` and `require_user()`.
- **Strict Data Ownership Verification:** `user/view_report.php` enforces cryptographic patient ownership through `get_owned_report()`. Users cannot view another patient's data by guessing report IDs.
- **SQL Injection Prevention:** 100% of database interactions run through **PDO Prepared Statements**.
- **Cross-Site Request Forgery (CSRF) Protection:** State-modifying actions validate one-time session-bound tokens (`csrf_field()` / `verify_csrf()`).
- **XSS Mitigation:** All dynamic inputs are sanitized using context-aware HTML escaping (`e()`).
- **Password Security:** Credentials hashed using industry-standard `bcrypt` (`password_hash` & `password_verify`).
- **Session Hardening:** Cookies enforced with `HttpOnly`, `SameSite=Lax`, strict session modes, and ID regeneration on privilege changes.
- **Audit Logging:** Built-in `activity_log` tracking security events, administrative updates, and user logins.

---

## 🔑 Demo Credentials

Once the database is imported, you can immediately test both roles:

| Role | Access URL | Username | Password |
| :--- | :--- | :--- | :--- |
| **Administrator** | `/auth/admin_login.php` | `admin` | `Admin@123` |
| **Patient / User** | `/auth/login.php` | `patient1` | `User@123` |

> *A demo doctor (**Dr. Sumit Ujgaonkar**, Care Plus, Kalmeshwar, Nagpur), reference ranges, and finalized diagnostic reports are pre-seeded.*

---

## 🚀 Installation & Quick Start

### Prerequisites
- **PHP:** Version 8.0 or higher
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Web Server:** Apache (via XAMPP, WAMP, LAMP, or MAMP)

### Step-by-Step Setup

1. **Clone or Download the Project:**
   ```bash
   git clone https://github.com/your-username/hospital-management-system.git
   ```
   *Or extract the project folder directly into your web root (e.g. `C:\xampp\htdocs\hospital-management-system`).*

2. **Start Services:**
   - Launch your **XAMPP / WAMP Control Panel**.
   - Start both **Apache** and **MySQL** services.

3. **Import Database:**
   - Navigate to **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Create a new database named `hospital_management` (or let the script create it).
   - Click **Import** > Select `database/schema.sql` > Click **Go**.
   - *(Optional for existing databases)*: Import `database/book_my_care.sql` if updating an existing schema.

4. **Verify Configuration:**
   - Check `config/db.php` for database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'hospital_management');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // default empty password for XAMPP
     ```
   - Check `config/config.php` for base URL path:
     ```php
     define('BASE_URL', '/hospital-management-system');
     ```

5. **Launch Application:**
   Open your browser and visit:
   ```text
   http://localhost/hospital-management-system/
   ```

---

## 📂 Directory Structure

```text
hospital-management-system/
├── index.php                 # Central landing page & role navigation
├── config/
│   ├── config.php            # Security headers, timezone, BASE_URL & constants
│   └── db.php                # PDO database connection handler
├── includes/
│   ├── auth_check.php        # Role-based middleware & route protection
│   ├── functions.php         # Business logic, helpers, CSRF, appointments
│   ├── header.php            # Global UI navigation & styles
│   ├── footer.php            # Shared footer & script inclusions
│   └── report_template.php   # Printable health report layout
├── auth/
│   ├── admin_login.php       # Dedicated admin authentication
│   ├── login.php             # Patient login portal
│   ├── register.php          # New patient self-registration
│   ├── logout.php            # Safe session termination
│   └── book_login.php        # Booking redirect handler
├── admin/
│   ├── dashboard.php         # Analytics, stats, and quick actions
│   ├── patients.php          # Patient list & management
│   ├── add_patient.php       # Register new patient
│   ├── edit_patient.php      # Edit patient profile
│   ├── doctors.php           # Doctor list & directory
│   ├── add_doctor.php        # Register doctor
│   ├── appointments.php      # Slot schedule & appointment oversight
│   ├── booking_report.php    # Daily and 24-hour booking analytics
│   ├── generate_report.php   # 10-test clinical diagnostic generator
│   ├── reports.php           # Master clinical records list
│   ├── view_report.php       # Diagnostic view & PDF print trigger
│   ├── references.php        # Configure normal/abnormal test ranges
│   └── users.php             # System user account administration
├── user/
│   ├── dashboard.php         # Patient dashboard & health summary
│   ├── profile.php           # View/edit personal profile details
│   ├── reports.php           # Patient's personal diagnostic history
│   └── view_report.php       # Ownership-verified report viewer
├── book/
│   ├── index.php             # Public appointment booking engine
│   └── cancel.php            # Patient self-cancellation handler
├── assets/
│   ├── css/style.css         # Modern responsive design & print styles
│   └── js/main.js            # Live search, confirmation dialogs, UI scripts
├── database/
│   ├── schema.sql            # Full schema with seed data
│   └── book_my_care.sql      # Appointments table migration script
├── .gitignore                # Git ignore rules for clean commits
└── README.md                 # Project documentation
```

---

## 🛠️ Technology Stack

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend** | PHP 8.0+ | Object-oriented PDO database integration, native sessions |
| **Database** | MySQL / MariaDB | Relational schema with foreign keys and cascade rules |
| **Frontend** | HTML5, CSS3, JavaScript | Custom responsive layout with print stylesheets |
| **Reporting** | Browser Print Engine | Native high-fidelity PDF generation via CSS `@media print` |
| **Architecture** | MVC-Inspired Modular | Decoupled admin, user, and public modules |

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

<div align="center">
  <sub>Developed with ❤️ for streamlined healthcare management.</sub>
</div>
