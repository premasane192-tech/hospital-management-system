<?php
require_once __DIR__ . '/../config/config.php';

$role = $_GET['role'] ?? '';

if ($role === 'admin' && is_admin_logged_in()) {
    log_activity($pdo, 'admin', $_SESSION['admin_id'], 'Logged out');
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
    session_regenerate_id(true);
    redirect(BASE_URL . '/auth/admin_login.php');
}

if ($role === 'user' && is_user_logged_in()) {
    log_activity($pdo, 'user', $_SESSION['user_id'], 'Logged out');
    unset($_SESSION['user_id'], $_SESSION['user_name']);
    session_regenerate_id(true);
    redirect(BASE_URL . '/auth/login.php');
}

// Fallback: clear everything
session_unset();
session_destroy();
redirect(BASE_URL . '/index.php');
