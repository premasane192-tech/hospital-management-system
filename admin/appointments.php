<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Appointments';

$date = clean($_GET['date'] ?? '');
$sql = "SELECT a.*, p.full_name AS patient_name, p.patient_code, p.phone, d.full_name AS doctor_name
        FROM appointments a
        JOIN patients p ON p.patient_id = a.patient_id
        JOIN doctors d ON d.doctor_id = a.doctor_id";
$params = [];
if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $sql .= " WHERE a.appointment_date = ?"; $params[] = $date; }
else $date = '';
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time ASC LIMIT 200";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

require __DIR__ . '/../includes/header_admin.php';
?>
<form method="get" class="dl-form" style="margin-bottom:18px;">
  <div class="field"><label for="date">Filter by date</label><input type="date" id="date" name="date" value="<?= e($date) ?>"></div>
  <button class="btn btn-primary btn-sm" type="submit">Show</button>
  <?php if ($date): ?><a class="btn btn-outline btn-sm" href="appointments.php">Clear</a><?php endif; ?>
</form>
<div class="table-wrap">
<table class="data-table">
  <thead><tr><th>Code</th><th>Date &amp; time</th><th>Patient</th><th>Phone</th><th>Doctor</th><th>Reason</th><th>Status</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="empty-state"><div class="icon">📅</div>No appointments found.</td></tr><?php endif; ?>
  <?php foreach ($rows as $a): ?>
    <tr>
      <td class="mono"><?= e($a['appointment_code']) ?></td>
      <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?> · <?= e(date('h:i A', strtotime($a['appointment_time']))) ?></td>
      <td><?= e($a['patient_name']) ?> <span class="hint mono">(<?= e($a['patient_code']) ?>)</span></td>
      <td><?= e($a['phone']) ?></td>
      <td><?= e($a['doctor_name']) ?></td>
      <td><?= e($a['reason']) ?></td>
      <td><?= $a['status'] === 'cancelled' ? '<span class="pill pill-high">Cancelled</span>' : '<span class="pill pill-normal">Booked</span>' ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
