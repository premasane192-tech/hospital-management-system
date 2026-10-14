<?php
require_once __DIR__ . '/../config/config.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT hr.*, p.full_name AS patient_name, p.patient_code, p.gender, p.date_of_birth, p.phone AS patient_phone,
            d.full_name AS doctor_name, d.registration_no, d.qualification, d.specialization, d.clinic_name
     FROM health_reports hr
     JOIN patients p ON p.patient_id = hr.patient_id
     LEFT JOIN doctors d ON d.doctor_id = hr.doctor_id
     WHERE hr.report_id = ?"
);
$stmt->execute([$id]);
$report = $stmt->fetch();
if (!$report) { flash('error', 'Report not found.'); redirect(BASE_URL . '/admin/reports.php'); }

$results = $pdo->prepare("SELECT * FROM test_results WHERE report_id = ?");
$results->execute([$id]);
$results = $results->fetchAll();

$age = $report['date_of_birth'] ? floor((time() - strtotime($report['date_of_birth'])) / 31556926) : null;
$pageTitle = 'Report ' . $report['report_code'];

require __DIR__ . '/../includes/header_admin.php';
require __DIR__ . '/../includes/report_body.php';
require __DIR__ . '/../includes/footer.php';
