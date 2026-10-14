<?php
require_once __DIR__ . '/../config/config.php';
require_user();
$pageTitle = 'My Profile';

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $phone = clean($_POST['phone'] ?? '');
    $address = clean($_POST['address'] ?? '');

    $pdo->prepare("UPDATE users SET phone = ? WHERE user_id = ?")->execute([$phone, $_SESSION['user_id']]);
    if ($patient) {
        $pdo->prepare("UPDATE patients SET phone = ?, address = ? WHERE patient_id = ?")
            ->execute([$phone, $address, $patient['patient_id']]);
    }
    flash('success', 'Profile updated.');
    redirect(BASE_URL . '/user/profile.php');
}

require __DIR__ . '/../includes/header_user.php';
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>

<div class="grid grid-2" style="align-items:start;">
  <div class="card">
    <h3 style="margin-top:0;">Account details</h3>
    <table style="width:100%; font-size:.9rem;">
      <tr><td style="color:var(--ink-soft); padding:6px 0; width:40%;">Full name</td><td><?= e($user['full_name']) ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Username</td><td class="mono"><?= e($user['username']) ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Email</td><td><?= e($user['email']) ?></td></tr>
      <?php if ($patient): ?>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Patient code</td><td class="mono"><?= e($patient['patient_code']) ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Gender</td><td><?= e($patient['gender']) ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Date of birth</td><td><?= e($patient['date_of_birth'] ?: '—') ?></td></tr>
      <tr><td style="color:var(--ink-soft); padding:6px 0;">Blood group</td><td><?= e($patient['blood_group'] ?: '—') ?></td></tr>
      <?php endif; ?>
    </table>
    <p class="hint" style="margin-top:14px;">Name, gender, and date of birth are managed by hospital staff to keep your medical record accurate. Contact the front desk to update these.</p>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Contact details</h3>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label>Phone</label><input type="text" name="phone" value="<?= e($user['phone']) ?>"></div>
      <div class="field"><label>Address</label><input type="text" name="address" value="<?= e($patient['address'] ?? '') ?>"></div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
