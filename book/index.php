<?php
require_once __DIR__ . '/../config/config.php';
// Book My Care is open to everyone - no login. Bookings made in this browser are
// remembered in the session so the visitor can view or cancel their own.
$pageTitle = 'Book My Care';
$_SESSION['bmc_appts'] = $_SESSION['bmc_appts'] ?? [];
$myIds = array_map('intval', $_SESSION['bmc_appts']);

$doctors = $pdo->query("SELECT doctor_id, full_name, specialization, clinic_name, address, phone FROM doctors ORDER BY full_name")->fetchAll();
$doctorsById = array_column($doctors, null, 'doctor_id');
$dates = bmc_bookable_dates();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name     = mb_substr(clean($_POST['full_name'] ?? ''), 0, 100);
    $phone    = mb_substr(clean($_POST['phone'] ?? ''), 0, 20);
    $doctorId = (int)($_POST['doctor_id'] ?? 0);
    $date     = clean($_POST['appointment_date'] ?? '');
    $reason   = mb_substr(clean($_POST['reason'] ?? ''), 0, 255);

    if ($name === '') $errors[] = 'Please enter your name.';
    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
    if (!isset($doctorsById[$doctorId])) $errors[] = 'Please choose a doctor.';
    if (!in_array($date, $dates, true))   $errors[] = 'Please choose a valid date (today up to ' . BMC_DAYS_AHEAD . ' days ahead, not Sundays).';

    $patientId = null;
    if (!$errors) {
        // reuse the patient record with the same name + phone, otherwise create a walk-in record
        $f = $pdo->prepare("SELECT patient_id FROM patients WHERE phone = ? AND LOWER(full_name) = LOWER(?) LIMIT 1");
        $f->execute([$phone, $name]);
        $patientId = $f->fetchColumn() ?: null;
        if (!$patientId) {
            $pc = $pdo->prepare("INSERT INTO patients (user_id, patient_code, full_name, gender, phone) VALUES (NULL, ?, ?, 'Other', ?)");
            $pc->execute([generate_patient_code($pdo), $name, $phone]);
            $patientId = (int)$pdo->lastInsertId();
        }
        // one active booking per patient, doctor and day
        $dup = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND doctor_id = ? AND appointment_date = ? AND status = 'booked'");
        $dup->execute([$patientId, $doctorId, $date]);
        if ($dup->fetchColumn()) $errors[] = 'You already have an appointment with this doctor on that day.';
    }

    if (!$errors) {
        $newId = null;
        // Try the earliest free slot; if someone grabs it at the same instant, the UNIQUE key rejects it and we try again.
        for ($try = 0; $try < 5 && !$newId; $try++) {
            $free = bmc_free_slots($pdo, $doctorId, $date);
            if (!$free) break;
            try {
                $ins = $pdo->prepare("INSERT INTO appointments (appointment_code, patient_id, doctor_id, appointment_date, appointment_time, reason)
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $ins->execute([generate_appointment_code($pdo, $date), $patientId, $doctorId, $date, $free[0] . ':00', $reason ?: null]);
                $newId = (int)$pdo->lastInsertId();
            } catch (PDOException $ex) {
                if ($ex->getCode() !== '23000') throw $ex;   // not a duplicate-slot clash
            }
        }
        if ($newId) {
            $_SESSION['bmc_appts'][] = $newId;
            log_activity($pdo, 'user', 0, 'Booked appointment #' . $newId . ' (Book My Care guest)');
            flash('success', 'Your appointment is booked.');
            redirect(BASE_URL . '/book/index.php?done=' . $newId);
        }
        $errors[] = 'Sorry, no free time is left on that day. Please choose another date.';
    }
}

// Appointment just booked (confirmation card) — must belong to this patient
$confirm = null;
if ($myIds && isset($_GET['done']) && in_array((int)$_GET['done'], $myIds, true)) {
    $c = $pdo->prepare("SELECT a.*, d.full_name AS doctor_name, d.specialization, d.clinic_name, d.address, d.phone,
                               p.full_name AS patient_name, p.patient_code
                        FROM appointments a JOIN doctors d ON d.doctor_id = a.doctor_id
                        JOIN patients p ON p.patient_id = a.patient_id
                        WHERE a.appointment_id = ?");
    $c->execute([(int)$_GET['done']]);
    $confirm = $c->fetch() ?: null;
}

$mine = [];
if ($myIds) {
    $in = implode(',', array_fill(0, count($myIds), '?'));
    $m = $pdo->prepare("SELECT a.*, d.full_name AS doctor_name FROM appointments a
                        JOIN doctors d ON d.doctor_id = a.doctor_id
                        WHERE a.appointment_id IN ($in) ORDER BY a.appointment_date DESC, a.appointment_time DESC");
    $m->execute($myIds);
    $mine = $m->fetchAll();
}

require __DIR__ . '/../includes/header_book.php';
$hospital = HOSPITAL_NAME;
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error" data-autohide><?= e($msg) ?></div><?php endif; ?>
<?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>

<?php if ($confirm): ?>
<div class="card" id="slip" style="border-color:var(--primary);margin-bottom:22px;">
  <p class="eyebrow" style="margin:0 0 6px;">Appointment confirmed · <?= e($confirm['appointment_code']) ?></p>
  <h3 style="margin:0 0 4px;">Report to <?= e($confirm['clinic_name'] ?: $hospital) ?> at
    <span style="color:var(--primary);"><?= e(date('h:i A', strtotime($confirm['appointment_time']))) ?></span></h3>
  <p style="margin:0 0 14px;font-size:1.05rem;"><strong><?= e(date('l, d F Y', strtotime($confirm['appointment_date']))) ?></strong></p>
  <div class="grid grid-2">
    <div><span class="hint">Patient</span><br><strong><?= e($confirm['patient_name']) ?></strong> <span class="hint">(<?= e($confirm['patient_code']) ?>)</span></div>
    <div><span class="hint">Doctor</span><br><strong><?= e($confirm['doctor_name']) ?></strong><?= $confirm['specialization'] ? ' · ' . e($confirm['specialization']) : '' ?></div>
    <div><span class="hint">Hospital</span><br><strong><?= e($confirm['clinic_name'] ?: $hospital) ?></strong><?= $confirm['address'] ? '<br>' . e($confirm['address']) : '' ?><?= $confirm['phone'] ? '<br>📞 ' . e($confirm['phone']) : '' ?></div>
    <div><span class="hint">Please note</span><br>Arrive <?= BMC_ARRIVE_EARLY ?> minutes early (by <?= e(date('h:i A', strtotime($confirm['appointment_time']) - BMC_ARRIVE_EARLY * 60)) ?>) and tell the front desk your appointment code.</div>
  </div>
  <p style="margin:16px 0 0;"><button type="button" class="btn btn-outline btn-sm" onclick="window.print()">🖨️ Print slip</button></p>
</div>
<?php endif; ?>

<div class="card" style="margin-bottom:22px;">
  <h3 style="margin-top:0;">Book an appointment</h3>
  <p class="hint" style="margin-top:0;">Pick a doctor and a day. You will be given the earliest free time on that day (<?= BMC_SLOT_MIN ?>-minute visits, Monday–Saturday).</p>
  <?php if (!$doctors): ?>
    <div class="alert alert-info">No doctors are available for booking yet. Please contact the hospital.</div>
  <?php else: ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field">
        <label for="full_name">Your name</label>
        <input type="text" id="full_name" name="full_name" required maxlength="100" autocomplete="name" value="<?= e($_POST['full_name'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="phone">Phone number</label>
        <input type="tel" id="phone" name="phone" required maxlength="20" autocomplete="tel" value="<?= e($_POST['phone'] ?? '') ?>">
      </div>
    </div>
    <div class="field-row">
      <div class="field">
        <label for="doctor_id">Doctor</label>
        <select id="doctor_id" name="doctor_id" required>
          <?php foreach ($doctors as $d): ?>
            <option value="<?= (int)$d['doctor_id'] ?>" <?= (int)($_POST['doctor_id'] ?? 0) === (int)$d['doctor_id'] ? 'selected' : '' ?>><?= e($d['full_name']) ?><?= $d['specialization'] ? ' — ' . e($d['specialization']) : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="appointment_date">Date</label>
        <select id="appointment_date" name="appointment_date" required>
          <?php foreach ($dates as $dt): ?>
            <option value="<?= e($dt) ?>" <?= ($_POST['appointment_date'] ?? '') === $dt ? 'selected' : '' ?>><?= e(date('D, d M Y', strtotime($dt))) ?><?= $dt === date('Y-m-d') ? ' (today)' : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="field">
      <label for="reason">Reason for visit <span class="hint" style="text-transform:none;">(optional)</span></label>
      <input type="text" id="reason" name="reason" maxlength="255" placeholder="e.g. fever, check-up, report review" value="<?= e($_POST['reason'] ?? '') ?>">
    </div>
    <button type="submit" class="btn btn-accent">Book &amp; get my time</button>
  </form>
  <?php endif; ?>
</div>

<div class="section-title"><h3>My appointments <span class="hint" style="text-transform:none;">(booked from this browser)</span></h3></div>
<div class="table-wrap">
<table class="data-table">
  <thead><tr><th>Code</th><th>Date &amp; time</th><th>Doctor</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if (!$mine): ?>
    <tr><td colspan="5" class="empty-state"><div class="icon">📅</div>No appointments yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($mine as $a):
        $upcoming = $a['status'] === 'booked' && strtotime($a['appointment_date'] . ' ' . $a['appointment_time']) > time(); ?>
    <tr>
      <td class="mono"><?= e($a['appointment_code']) ?></td>
      <td><strong><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></strong> · <?= e(date('h:i A', strtotime($a['appointment_time']))) ?></td>
      <td><?= e($a['doctor_name']) ?></td>
      <td><?= $a['status'] === 'cancelled' ? '<span class="pill pill-high">Cancelled</span>' : ($upcoming ? '<span class="pill pill-normal">Booked</span>' : '<span class="pill pill-low">Past</span>') ?></td>
      <td>
        <?php if ($upcoming): ?>
        <a class="btn btn-outline btn-sm" href="index.php?done=<?= (int)$a['appointment_id'] ?>">View slip</a>
        <form method="post" action="cancel.php" style="display:inline;">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$a['appointment_id'] ?>">
          <button class="btn btn-outline btn-sm" type="submit" data-confirm="Cancel this appointment?">Cancel</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<style>@media print{.bmc-top,.bmc-hero,.ambient-bg,.card:not(#slip),.section-title,.table-wrap,.alert,.btn{display:none!important}}</style>
<?php require __DIR__ . '/../includes/footer_book.php'; ?>
