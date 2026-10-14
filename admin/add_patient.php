<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Register Patient';

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old = $_POST;

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
        $code = generate_patient_code($pdo);
        $stmt = $pdo->prepare(
            "INSERT INTO patients (patient_code, full_name, date_of_birth, gender, phone, email, address, blood_group, registered_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$code, $fullName, $dob ?: null, $gender, $phone, $email ?: null, $address, $bloodGrp, $_SESSION['admin_id']]);
        $newId = $pdo->lastInsertId();
        log_activity($pdo, 'admin', $_SESSION['admin_id'], "Registered patient $code ($fullName)");
        flash('success', "Patient registered successfully with code $code.");
        redirect(BASE_URL . '/admin/patient_view.php?id=' . $newId);
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="card" style="max-width:760px;">
  <?php foreach ($errors as $err): ?><div class="alert alert-error" data-autohide><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field"><label>Full name *</label><input type="text" name="full_name" required value="<?= e($old['full_name'] ?? '') ?>"></div>
      <div class="field"><label>Date of birth</label><input type="date" name="dob" value="<?= e($old['dob'] ?? '') ?>"></div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Gender *</label>
        <select name="gender" required>
          <option value="">Select…</option>
          <?php foreach (['Male','Female','Other'] as $g): ?>
            <option value="<?= $g ?>" <?= ($old['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Blood group</label><input type="text" name="blood_group" placeholder="e.g. O+" value="<?= e($old['blood_group'] ?? '') ?>"></div>
    </div>
    <div class="field-row">
      <div class="field"><label>Phone</label><input type="text" name="phone" value="<?= e($old['phone'] ?? '') ?>"></div>
      <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($old['email'] ?? '') ?>"></div>
    </div>
    <div class="field"><label>Address</label><input type="text" name="address" value="<?= e($old['address'] ?? '') ?>"></div>
    <button type="submit" class="btn btn-primary">Register Patient</button>
    <a href="patients.php" class="btn btn-outline">Cancel</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
