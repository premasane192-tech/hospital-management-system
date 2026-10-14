<?php
/**
 * Global bootstrap: session, security headers, base path.
 * Every protected page includes this file FIRST.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

// Basic hardening headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

date_default_timezone_set('Asia/Kolkata');
define('BASE_URL', '/hospital-management-system'); // adjust to your local path if needed
define('APP_NAME', 'Care Plus Hospital Management System');
define('HOSPITAL_NAME', 'Care Plus');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
