<?php
require_once __DIR__ . '/../config/config.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();
if (!$patient) { flash('error', 'Patient not found.'); redirect(BASE_URL . '/admin/patients.php'); }

$pageTitle = $patient['full_name'];

$reports = $pdo->prepare(
    "SELECT hr.*, d.full_name AS doctor_name FROM health_reports hr
     LEFT JOIN doctors d ON d.doctor_id = hr.doctor_id
     WHERE hr.patient_id = ? ORDER BY hr.report_date DESC"
);
$reports->execute([$id]);
$reports = $reports->fetchAll();

require __DIR__ . '/../includes/header_admin.php';
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>

<div class="grid grid-2" style="align-items:start;">
  <div class="card">
    <div class="section-title" style="margin-top:0;">
      <h3><?= e($patient['full_name']) ?></h3>
      <a href="edit_patient.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Edit</a>
    </div>
    <p class="mono hint">Patient code: <?= e($patient['patient_code']) ?></p>
    <table style="width:100%; font-size:.9rem;">
      <tr><td style="color:var(--ink-soft); padding:6px 0; width:40%;">Date of birth</td><td><?= e($patient['date_of_birth'] ?: '—') ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Gender</td><td><?= e($patient['gender']) ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Blood group</td><td><?= e($patient['blood_group'] ?: '—') ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Phone</td><td><?= e($patient['phone'] ?: '—') ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Email</td><td><?= e($patient['email'] ?: '—') ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Address</td><td><?= e($patient['address'] ?: '—') ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Login account</td><td><?= $patient['user_id'] ? '<span class="pill pill-normal">Linked</span>' : '<span class="pill pill-low">No login</span>' ?></td></tr>
    </table>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Generate a new report</h3>
    <p>Run diagnostic panels for this patient and produce a doctor-reviewed health report.</p>
    <a href="generate_report.php?patient_id=<?= $id ?>" class="btn btn-primary">🧪 New Health Report</a>
  </div>
</div>

<div class="section-title"><h3>Report history (<?= count($reports) ?>)</h3></div>
<div class="table-wrap">
<table class="data-table">
  <thead><tr><th>Report Code</th><th>Date</th><th>Doctor</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if (!$reports): ?>
    <tr><td colspan="5" class="empty-state">No reports generated for this patient yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($reports as $r): ?>
    <tr>
      <td class="mono"><?= e($r['report_code']) ?></td>
      <td><?= e(date('d M Y', strtotime($r['report_date']))) ?></td>
      <td><?= e($r['doctor_name'] ?? '—') ?></td>
      <td><span class="pill pill-normal"><?= e(ucfirst($r['status'])) ?></span></td>
      <td><a href="view_report.php?id=<?= (int)$r['report_id'] ?>" class="btn btn-outline btn-sm">View / PDF</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
