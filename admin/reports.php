<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Health Reports';

$reports = $pdo->query(
    "SELECT hr.*, p.full_name AS patient_name, p.patient_code, d.full_name AS doctor_name
     FROM health_reports hr
     JOIN patients p ON p.patient_id = hr.patient_id
     LEFT JOIN doctors d ON d.doctor_id = hr.doctor_id
     ORDER BY hr.created_at DESC"
)->fetchAll();

require __DIR__ . '/../includes/header_admin.php';
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error" data-autohide><?= e($msg) ?></div><?php endif; ?>

<div class="card download-panel reveal-now" id="monthly-download">
  <div class="dl-head">
    <div class="dl-icon">⬇️</div>
    <div><h3 style="margin:0;">Download monthly reports</h3>
    <p class="hint" style="margin:2px 0 0;">Every report for the chosen month, across all patients — with each test value, status and doctor notes.</p></div>
  </div>
  <form method="get" action="download_monthly.php" class="dl-form" data-download-form>
    <div class="field"><label>Month</label><input type="month" name="month" value="<?= e(date('Y-m')) ?>" max="<?= e(date('Y-m')) ?>"></div>
    <div class="field"><label>Format</label>
      <select name="format" data-format>
        <option value="csv">Excel / CSV (all test values)</option>
        <option value="print">PDF (print &amp; save)</option>
      </select></div>
    <label class="check"><input type="checkbox" name="all" value="1"> All months</label>
    <button type="submit" class="btn btn-primary">Download</button>
  </form>
</div>



<div class="section-title">
  <h3>All health reports (<?= count($reports) ?>)</h3>
  <a href="generate_report.php" class="btn btn-primary btn-sm">🧪 Generate Report</a>
</div>

<div class="search-bar">
  <input type="text" placeholder="Search by patient, code, or doctor…" data-search-input="#reportsTable">
</div>

<div class="table-wrap">
<table class="data-table" id="reportsTable">
  <thead><tr><th>Report Code</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if (!$reports): ?>
    <tr><td colspan="6" class="empty-state"><div class="icon">📄</div>No reports generated yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($reports as $r): ?>
    <tr>
      <td class="mono"><?= e($r['report_code']) ?></td>
      <td><?= e($r['patient_name']) ?> <span class="hint mono">(<?= e($r['patient_code']) ?>)</span></td>
      <td><?= e($r['doctor_name'] ?? '—') ?></td>
      <td><?= e(date('d M Y', strtotime($r['report_date']))) ?></td>
      <td><span class="pill pill-normal"><?= e(ucfirst($r['status'])) ?></span></td>
      <td><a href="view_report.php?id=<?= (int)$r['report_id'] ?>" class="btn btn-outline btn-sm">View / PDF</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
