<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Edit Patient';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();
if (!$patient) { flash('error', 'Patient not found.'); redirect(BASE_URL . '/admin/patients.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = clean($_POST['full_name'] ?? '');
    $dob      = $_POST['dob'] ?? '';
    $gender   = $_POST['gender'] ?? '';
    $phone    = clean($_POST['phone'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $address  = clean($_POST['address'] ?? '');
    $bloodGrp = clean($_POST['blood_group'] ?? '');

    if ($fullName === '' || !in_array($gender, ['Male', 'Female', 'Other'], true)) {
        $errors[] = 'Full name and gender are required.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            "UPDATE patients SET full_name=?, date_of_birth=?, gender=?, phone=?, email=?, address=?, blood_group=? WHERE patient_id=?"
        );
        $stmt->execute([$fullName, $dob ?: null, $gender, $phone, $email ?: null, $address, $bloodGrp, $id]);
        log_activity($pdo, 'admin', $_SESSION['admin_id'], "Edited patient #$id ($fullName)");
        flash('success', 'Patient details updated.');
        redirect(BASE_URL . '/admin/patient_view.php?id=' . $id);
    }
    $patient = array_merge($patient, $_POST);
}

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="card" style="max-width:760px;">
  <p class="mono hint">Patient code: <?= e($patient['patient_code']) ?></p>
  <?php foreach ($errors as $err): ?><div class="alert alert-error" data-autohide><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field"><label>Full name *</label><input type="text" name="full_name" required value="<?= e($patient['full_name']) ?>"></div>
      <div class="field"><label>Date of birth</label><input type="date" name="dob" value="<?= e($patient['date_of_birth']) ?>"></div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Gender *</label>
        <select name="gender" required>
          <?php foreach (['Male','Female','Other'] as $g): ?>
            <option value="<?= $g ?>" <?= $patient['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Blood group</label><input type="text" name="blood_group" value="<?= e($patient['blood_group']) ?>"></div>
    </div>
    <div class="field-row">
      <div class="field"><label>Phone</label><input type="text" name="phone" value="<?= e($patient['phone']) ?>"></div>
      <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($patient['email']) ?>"></div>
    </div>
    <div class="field"><label>Address</label><input type="text" name="address" value="<?= e($patient['address']) ?>"></div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="patient_view.php?id=<?= $id ?>" class="btn btn-outline">Cancel</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
