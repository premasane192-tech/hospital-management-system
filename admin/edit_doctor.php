<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Edit Doctor';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();
if (!$doctor) { flash('error', 'Doctor not found.'); redirect(BASE_URL . '/admin/doctors.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = clean($_POST['full_name'] ?? '');
    if ($fullName === '') {
        $errors[] = 'Doctor name is required.';
    } else {
        $stmt = $pdo->prepare(
            "UPDATE doctors SET full_name=?, registration_no=?, qualification=?, specialization=?, clinic_name=?, phone=?, address=?, description=?
             WHERE doctor_id=?"
        );
        $stmt->execute([
            $fullName,
            clean($_POST['registration_no'] ?? ''),
            clean($_POST['qualification'] ?? ''),
            clean($_POST['specialization'] ?? ''),
            clean($_POST['clinic_name'] ?? ''),
            clean($_POST['phone'] ?? ''),
            clean($_POST['address'] ?? ''),
            clean($_POST['description'] ?? ''),
            $id,
        ]);
        log_activity($pdo, 'admin', $_SESSION['admin_id'], "Edited doctor #$id ($fullName)");
        flash('success', 'Doctor details updated.');
        redirect(BASE_URL . '/admin/doctors.php');
    }
    $doctor = array_merge($doctor, $_POST);
}

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="card" style="max-width:760px;">
  <?php foreach ($errors as $err): ?><div class="alert alert-error" data-autohide><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field"><label>Doctor name *</label><input type="text" name="full_name" required value="<?= e($doctor['full_name']) ?>"></div>
      <div class="field"><label>Registration number</label><input type="text" name="registration_no" value="<?= e($doctor['registration_no']) ?>"></div>
    </div>
    <div class="field-row">
      <div class="field"><label>Qualification</label><input type="text" name="qualification" value="<?= e($doctor['qualification']) ?>"></div>
      <div class="field"><label>Specialization</label><input type="text" name="specialization" value="<?= e($doctor['specialization']) ?>"></div>
    </div>
    <div class="field-row">
      <div class="field"><label>Clinic / Hospital name</label><input type="text" name="clinic_name" value="<?= e($doctor['clinic_name']) ?>"></div>
      <div class="field"><label>Phone</label><input type="text" name="phone" value="<?= e($doctor['phone']) ?>"></div>
    </div>
    <div class="field"><label>Address</label><input type="text" name="address" value="<?= e($doctor['address']) ?>"></div>
    <div class="field"><label>Clinical description / notes</label><textarea name="description"><?= e($doctor['description']) ?></textarea></div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="doctors.php" class="btn btn-outline">Cancel</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
