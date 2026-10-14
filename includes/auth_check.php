<?php
/**
 * Role-based access control.
 * Call the relevant require_*() function at the TOP of every protected page,
 * before any HTML or query is executed.
 */

function is_admin_logged_in() {
    return !empty($_SESSION['admin_id']);
}

function is_user_logged_in() {
    return !empty($_SESSION['user_id']);
}

/** Protect all /admin pages */
function require_admin() {
    if (!is_admin_logged_in()) {
        redirect(BASE_URL . '/auth/admin_login.php');
    }
}

/** Protect all /user pages */
function require_user() {
    if (!is_user_logged_in()) {
        redirect(BASE_URL . '/auth/login.php');
    }
}

/**
 * Ownership check: confirms the report requested belongs to the
 * currently logged-in patient/user. This is what stops
 * report.php?id=105 style access to someone else's data.
 *
 * Returns the report row (array) on success, or null if the report
 * does not exist / does not belong to this user — caller must handle
 * the null case by refusing access (never trust the URL alone).
 */
function get_owned_report(PDO $pdo, int $reportId, int $userId) {
    $stmt = $pdo->prepare(
        "SELECT hr.* FROM health_reports hr
         JOIN patients p ON p.patient_id = hr.patient_id
         WHERE hr.report_id = ? AND p.user_id = ?"
    );
    $stmt->execute([$reportId, $userId]);
    return $stmt->fetch() ?: null;
}
