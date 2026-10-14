<?php
require_once __DIR__ . '/../config/config.php';
require_user();
require_once __DIR__ . '/../includes/charts.php';
$pageTitle = 'My Dashboard';

$stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();

$u = $pdo->prepare("SELECT username, email, phone FROM users WHERE user_id = ?");
$u->execute([$_SESSION['user_id']]);
$account = $u->fetch() ?: [];

$reports = [];
$latest = null;
if ($patient) {
    $r = $pdo->prepare("SELECT hr.*, d.full_name AS doctor_name FROM health_reports hr LEFT JOIN doctors d ON d.doctor_id = hr.doctor_id WHERE hr.patient_id = ? ORDER BY hr.report_date DESC");
    $r->execute([$patient['patient_id']]);
    $reports = $r->fetchAll();
    $latest = $reports[0] ?? null;
}

$thisMonth = 0;
foreach ($reports as $rp) if (date('Y-m', strtotime($rp['report_date'])) === date('Y-m')) $thisMonth++;
$age = ($patient && $patient['date_of_birth']) ? floor((time() - strtotime($patient['date_of_birth'])) / 31556926) : null;

$series = $patient ? month_series($pdo, (int)$patient['patient_id']) : [];
$mix    = $latest ? status_mix($pdo, (int)$latest['report_id']) : [];

require __DIR__ . '/../includes/header_user.php';
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>

<?php if (!$patient): ?>
  <div class="empty-state"><div class="icon">🗂️</div>
    Your account isn't linked to a patient record yet. Please contact the hospital administrator.
  </div>
<?php else: ?>

<div class="welcome-banner">
  <div class="avatar avatar-lg avatar-ring"><?= e(strtoupper(substr($patient['full_name'], 0, 1))) ?></div>
  <div class="wb-main">
    <p class="wb-eyebrow" data-greeting>Welcome back</p>
    <h2><?= e($patient['full_name']) ?></h2>
    <div class="info-chips">
      <span class="info-chip">🧾 <?= e($patient['patient_code']) ?></span>
      <?php if (!empty($account['username'])): ?><span class="info-chip">👤 @<?= e($account['username']) ?></span><?php endif; ?>
      <?php if (!empty($account['email'])): ?><span class="info-chip">✉️ <?= e($account['email']) ?></span><?php endif; ?>
      <?php if ($patient['phone']): ?><span class="info-chip">📞 <?= e($patient['phone']) ?></span><?php endif; ?>
      <span class="info-chip">⚧ <?= e($patient['gender']) ?><?= $age !== null ? ' · ' . e($age) . ' yrs' : '' ?></span>
      <?php if ($patient['blood_group']): ?><span class="info-chip">🩸 <?= e($patient['blood_group']) ?></span><?php endif; ?>
    </div>
  </div>
</div>

<div class="grid grid-4">
  <div class="stat-card"><div class="stat-icon">🧾</div><div class="stat-label">Patient Code</div><div class="stat-value mono" style="font-size:1.3rem;"><?= e($patient['patient_code']) ?></div></div>
  <div class="stat-card"><div class="stat-icon">📄</div><div class="stat-label">Total Reports</div><div class="stat-value" data-count="<?= count($reports) ?>"><?= count($reports) ?></div></div>
  <div class="stat-card"><div class="stat-icon">🗓️</div><div class="stat-label">Reports This Month</div><div class="stat-value" data-count="<?= (int)$thisMonth ?>"><?= (int)$thisMonth ?></div></div>
  <div class="stat-card"><div class="stat-icon">🩸</div><div class="stat-label">Blood Group</div><div class="stat-value" style="font-size:1.5rem;"><?= e($patient['blood_group'] ?: '—') ?></div></div>
</div>

<div class="grid grid-2" style="margin-top:22px;">
  <div class="card"><h3 style="margin-top:0;">My reports — last 6 months</h3><?php render_bar_chart($series); ?></div>
  <div class="card"><h3 style="margin-top:0;">Latest report at a glance</h3>
    <p class="hint">How your most recent test results compare with normal ranges.</p>
    <?php if ($latest) render_status_mix($mix); else echo '<div class="empty-state" style="padding:24px;">No reports yet.</div>'; ?></div>
</div>

<div class="section-title"><h3>Latest report</h3></div>
<?php if ($latest): ?>
  <div class="card">
    <div class="section-title" style="margin-top:0;">
      <div>
        <strong class="mono"><?= e($latest['report_code']) ?></strong>
        <p class="hint" style="margin:2px 0 0;">Reviewed by <?= e($latest['doctor_name'] ?? 'hospital staff') ?> on <?= e(date('d M Y', strtotime($latest['report_date']))) ?></p>
      </div>
      <a href="view_report.php?id=<?= (int)$latest['report_id'] ?>" class="btn btn-primary btn-sm">View / Download PDF</a>
    </div>
  </div>
<?php else: ?>
  <div class="empty-state"><div class="icon">🧪</div>No reports have been generated for you yet.</div>
<?php endif; ?>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
