<?php
require_once __DIR__ . '/../config/config.php';
require_user();
$pageTitle = 'My Reports';

$stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();

$reports = [];
if ($patient) {
    $r = $pdo->prepare(
        "SELECT hr.*, d.full_name AS doctor_name FROM health_reports hr
         LEFT JOIN doctors d ON d.doctor_id = hr.doctor_id
         WHERE hr.patient_id = ? ORDER BY hr.report_date DESC"
    );
    $r->execute([$patient['patient_id']]);
    $reports = $r->fetchAll();
}

require __DIR__ . '/../includes/header_user.php';
?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error" data-autohide><?= e($msg) ?></div><?php endif; ?>

<div class="section-title"><h3>My reports (<?= count($reports) ?>)</h3></div>

<div class="table-wrap">
<table class="data-table">
  <thead><tr><th>Report Code</th><th>Date</th><th>Doctor</th><th></th></tr></thead>
  <tbody>
  <?php if (!$reports): ?>
    <tr><td colspan="4" class="empty-state"><div class="icon">🧪</div>No reports available yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($reports as $r): ?>
    <tr>
      <td class="mono"><?= e($r['report_code']) ?></td>
      <td><?= e(date('d M Y', strtotime($r['report_date']))) ?></td>
      <td><?= e($r['doctor_name'] ?? '—') ?></td>
      <td><a href="view_report.php?id=<?= (int)$r['report_id'] ?>" class="btn btn-outline btn-sm">View / Download PDF</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
