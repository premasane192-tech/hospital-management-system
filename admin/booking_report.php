<?php
require_once __DIR__ . '/../config/config.php';
require_admin();   // admin-only: patients can never open this page
$pageTitle = 'Booking Report';

$day = clean($_GET['date'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) $day = date('Y-m-d');

/* ---- A) Last 24 hours: bookings MADE in the past 24 hours (rolling window) ---- */
$since = (new DateTime('now', new DateTimeZone(BMC_TZ)))->modify('-24 hours')->format('Y-m-d H:i:s');
$q = $pdo->prepare("SELECT COUNT(*) AS total,
                           SUM(status = 'booked')    AS booked,
                           SUM(status = 'cancelled') AS cancelled,
                           COUNT(DISTINCT patient_id) AS patients
                    FROM appointments WHERE created_at >= ?");
$q->execute([$since]);
$last24 = $q->fetch();

/* ---- B) Chosen day: appointments scheduled for that date ---- */
$q = $pdo->prepare("SELECT a.*, p.full_name AS patient_name, p.patient_code, p.phone, d.full_name AS doctor_name
                    FROM appointments a
                    JOIN patients p ON p.patient_id = a.patient_id
                    JOIN doctors d  ON d.doctor_id  = a.doctor_id
                    WHERE a.appointment_date = ? ORDER BY a.appointment_time ASC");
$q->execute([$day]);
$rows = $q->fetchAll();

$booked = array_values(array_filter($rows, fn($r) => $r['status'] === 'booked'));
$cancelled = count($rows) - count($booked);
$morning = $afternoon = 0;
$byDoctor = [];
foreach ($booked as $r) {
    ($r['appointment_time'] < '13:00:00') ? $morning++ : $afternoon++;
    $byDoctor[$r['doctor_name']] = ($byDoctor[$r['doctor_name']] ?? 0) + 1;
}
arsort($byDoctor);
$totalSlots = count(bmc_all_slots());

/* ---- CSV download of the chosen day ---- */
if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="booking-report-' . $day . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Code', 'Date', 'Time', 'Patient', 'Patient ID', 'Phone', 'Doctor', 'Reason', 'Status']);
    foreach ($rows as $r) fputcsv($out, [$r['appointment_code'], $r['appointment_date'], substr($r['appointment_time'], 0, 5),
        $r['patient_name'], $r['patient_code'], $r['phone'], $r['doctor_name'], $r['reason'], $r['status']]);
    exit;
}

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="section-title"><h3>Last 24 hours</h3></div>
<p class="hint" style="margin-top:0;">Bookings made on Book My Care since <?= e(date('d M Y, h:i A', strtotime($since))) ?> (rolling 24 hours).</p>
<div class="grid grid-4" style="margin-bottom:26px;">
  <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-label">Patients booked</div><div class="stat-value"><?= (int)$last24['patients'] ?></div></div>
  <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-label">Active bookings</div><div class="stat-value"><?= (int)$last24['booked'] ?></div></div>
  <div class="stat-card"><div class="stat-icon">❌</div><div class="stat-label">Cancelled</div><div class="stat-value"><?= (int)$last24['cancelled'] ?></div></div>
  <div class="stat-card"><div class="stat-icon">🧾</div><div class="stat-label">Total requests</div><div class="stat-value"><?= (int)$last24['total'] ?></div></div>
</div>

<div class="section-title"><h3>Day report</h3></div>
<form method="get" class="dl-form" style="margin-bottom:18px;">
  <div class="field"><label for="date">Appointment date</label><input type="date" id="date" name="date" value="<?= e($day) ?>"></div>
  <button class="btn btn-primary btn-sm" type="submit">Show</button>
  <a class="btn btn-outline btn-sm" href="?date=<?= e($day) ?>&amp;csv=1">⬇️ Download CSV</a>
  <button class="btn btn-outline btn-sm" type="button" onclick="window.print()">🖨️ Print</button>
</form>
<div class="grid grid-4" style="margin-bottom:22px;">
  <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-label">Patients booked</div><div class="stat-value"><?= count($booked) ?></div></div>
  <div class="stat-card"><div class="stat-icon">🌅</div><div class="stat-label">Morning (9 AM–12 PM)</div><div class="stat-value"><?= $morning ?></div></div>
  <div class="stat-card"><div class="stat-icon">🌇</div><div class="stat-label">Afternoon (2–7 PM)</div><div class="stat-value"><?= $afternoon ?></div></div>
  <div class="stat-card"><div class="stat-icon">🩺</div><div class="stat-label">Doctors booked</div><div class="stat-value"><?= count($byDoctor) ?></div></div>
</div>
<p class="hint"><?= e(date('l, d F Y', strtotime($day))) ?> · <?= $cancelled ?> cancelled</p>

<?php if ($byDoctor): ?>
<div class="table-wrap" style="margin-bottom:22px;"><table class="data-table">
  <thead><tr><th>Doctor</th><th>Patients booked</th><th>Free slots (of <?= $totalSlots ?>)</th></tr></thead><tbody>
  <?php foreach ($byDoctor as $doc => $n): ?><tr><td><?= e($doc) ?></td><td><strong><?= (int)$n ?></strong></td><td><?= max(0, $totalSlots - $n) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>

<div class="table-wrap"><table class="data-table">
  <thead><tr><th>Time</th><th>Patient</th><th>Phone</th><th>Doctor</th><th>Code</th><th>Status</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="6" class="empty-state"><div class="icon">📅</div>No bookings for this day.</td></tr><?php endif; ?>
  <?php foreach ($rows as $a): ?>
  <tr>
    <td><strong><?= e(date('h:i A', strtotime($a['appointment_time']))) ?></strong></td>
    <td><?= e($a['patient_name']) ?> <span class="hint mono">(<?= e($a['patient_code']) ?>)</span></td>
    <td><?= e($a['phone']) ?></td><td><?= e($a['doctor_name']) ?></td>
    <td class="mono"><?= e($a['appointment_code']) ?></td>
    <td><?= $a['status'] === 'cancelled' ? '<span class="pill pill-high">Cancelled</span>' : '<span class="pill pill-normal">Booked</span>' ?></td>
  </tr>
  <?php endforeach; ?>
</tbody></table></div>
<style>@media print{.sidebar,.topbar,.ambient-bg,.dl-form,.btn{display:none!important}.app-shell{display:block}}</style>
<?php require __DIR__ . '/../includes/footer.php'; ?>
