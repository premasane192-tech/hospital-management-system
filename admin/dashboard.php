<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
require_once __DIR__ . '/../includes/charts.php';
$pageTitle = 'Dashboard';

$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalDoctors  = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalReports  = $pdo->query("SELECT COUNT(*) FROM health_reports")->fetchColumn();

$recentPatients = $pdo->query(
    "SELECT * FROM patients ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

$recentReports = $pdo->query(
    "SELECT hr.report_id, hr.report_code, hr.report_date, hr.status, p.full_name AS patient_name, d.full_name AS doctor_name
     FROM health_reports hr
     JOIN patients p ON p.patient_id = hr.patient_id
     LEFT JOIN doctors d ON d.doctor_id = hr.doctor_id
     ORDER BY hr.created_at DESC LIMIT 6"
)->fetchAll();

$series = month_series($pdo);
$mix    = status_mix($pdo);

require __DIR__ . '/../includes/header_admin.php';
?>

<div class="welcome-banner">
  <div class="avatar avatar-lg avatar-ring"><?= e(strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1))) ?></div>
  <div class="wb-main">
    <p class="wb-eyebrow" data-greeting>Welcome back</p>
    <h2><?= e($_SESSION['admin_name'] ?? 'Admin') ?></h2>
    <div class="info-chips">
      <span class="info-chip" data-date></span>
      <span class="info-chip" data-clock></span>
      <span class="info-chip">🛡️ <?= e(ucfirst(str_replace('_', ' ', $_SESSION['admin_role'] ?? 'staff'))) ?></span>
    </div>
  </div>
  <a href="add_patient.php" class="btn btn-accent">➕ Register Patient</a>
</div>

<div class="grid grid-4">
  <div class="stat-card"><div class="stat-icon">🧑‍🤝‍🧑</div><div class="stat-label">Total Patients</div><div class="stat-value" data-count="<?= (int)$totalPatients ?>"><?= (int)$totalPatients ?></div></div>
  <div class="stat-card"><div class="stat-icon">👤</div><div class="stat-label">Total Users</div><div class="stat-value" data-count="<?= (int)$totalUsers ?>"><?= (int)$totalUsers ?></div></div>
  <div class="stat-card"><div class="stat-icon">🩺</div><div class="stat-label">Total Doctors</div><div class="stat-value" data-count="<?= (int)$totalDoctors ?>"><?= (int)$totalDoctors ?></div></div>
  <div class="stat-card"><div class="stat-icon">📄</div><div class="stat-label">Total Reports</div><div class="stat-value" data-count="<?= (int)$totalReports ?>"><?= (int)$totalReports ?></div></div>
</div>

<div class="section-title"><h3>Quick actions</h3></div>
<div class="grid grid-4">
  <a class="card" href="add_patient.php" style="text-decoration:none;">
    <strong style="color:var(--primary-dk);">➕ Register Patient</strong>
    <p style="margin:.5em 0 0;">Add a new patient to the system.</p>
  </a>
  <a class="card" href="generate_report.php" style="text-decoration:none;">
    <strong style="color:var(--primary-dk);">🧪 Generate Health Report</strong>
    <p style="margin:.5em 0 0;">Enter test results and produce a doctor-reviewed report.</p>
  </a>
  <a class="card" href="doctors.php" style="text-decoration:none;">
    <strong style="color:var(--primary-dk);">🩺 Manage Doctors</strong>
    <p style="margin:.5em 0 0;">Add or edit doctor descriptions and clinical notes.</p>
  </a>
  <a class="card" href="download_monthly.php?month=<?= e(date('Y-m')) ?>&format=csv" data-download style="text-decoration:none;">
    <strong style="color:var(--primary-dk);">⬇️ Download <?= e(date('F')) ?> Reports</strong>
    <p style="margin:.5em 0 0;">This month's reports as an Excel/CSV file.</p>
  </a>
</div>

<div class="grid grid-2" style="margin-top:22px;">
  <div class="card"><h3 style="margin-top:0;">Reports — last 6 months</h3><?php render_bar_chart($series); ?></div>
  <div class="card"><h3 style="margin-top:0;">Test results overview</h3>
    <p class="hint">Normal vs Low vs High across all reports.</p><?php render_status_mix($mix); ?></div>
</div>

<div class="section-title"><h3>Recently registered patients</h3><a href="patients.php" class="btn btn-outline btn-sm">View all</a></div>
<div class="table-wrap">
<table class="data-table">
  <thead><tr><th>Patient Code</th><th>Name</th><th>Gender</th><th>Phone</th><th>Registered</th></tr></thead>
  <tbody>
  <?php if (!$recentPatients): ?>
    <tr><td colspan="5" class="empty-state">No patients registered yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($recentPatients as $p): ?>
    <tr>
      <td class="mono"><?= e($p['patient_code']) ?></td>
      <td><a href="patient_view.php?id=<?= (int)$p['patient_id'] ?>"><?= e($p['full_name']) ?></a></td>
      <td><?= e($p['gender']) ?></td>
      <td><?= e($p['phone']) ?></td>
      <td><?= e(date('d M Y', strtotime($p['created_at']))) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="section-title"><h3>Recent reports</h3><a href="reports.php" class="btn btn-outline btn-sm">View all</a></div>
<div class="table-wrap">
<table class="data-table">
  <thead><tr><th>Report Code</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if (!$recentReports): ?>
    <tr><td colspan="6" class="empty-state">No reports generated yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($recentReports as $r): ?>
    <tr>
      <td class="mono"><?= e($r['report_code']) ?></td>
      <td><?= e($r['patient_name']) ?></td>
      <td><?= e($r['doctor_name'] ?? '—') ?></td>
      <td><?= e(date('d M Y', strtotime($r['report_date']))) ?></td>
      <td><span class="pill pill-normal"><?= e(ucfirst($r['status'])) ?></span></td>
      <td><a href="view_report.php?id=<?= (int)$r['report_id'] ?>" class="btn btn-outline btn-sm">View</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
