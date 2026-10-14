<?php
require_once __DIR__ . '/../config/config.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT full_name, patient_code FROM patients WHERE patient_id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if ($patient) {
    $del = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
    $del->execute([$id]);
    log_activity($pdo, 'admin', $_SESSION['admin_id'], "Deleted patient {$patient['patient_code']} ({$patient['full_name']})");
    flash('success', 'Patient record and associated reports deleted.');
} else {
    flash('error', 'Patient not found.');
}

redirect(BASE_URL . '/admin/patients.php');
