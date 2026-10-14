<?php
require_once __DIR__ . '/../config/config.php';

if (is_user_logged_in()) redirect(BASE_URL . '/user/dashboard.php');

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old = $_POST;

    $fullName = clean($_POST['full_name'] ?? '');
    $username = clean($_POST['username'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone    = clean($_POST['phone'] ?? '');
    $dob      = $_POST['dob'] ?? '';
    $gender   = $_POST['gender'] ?? '';
    $address  = clean($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $username === '' || $email === '' || $password === '') {
        $errors[] = 'Please fill in all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
        $errors[] = 'Please select a gender.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'That username or email is already registered. Try logging in instead.';
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, username, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$fullName, $username, $email, $phone, $hash]);
            $userId = $pdo->lastInsertId();

            $patientCode = generate_patient_code($pdo);
            $stmt = $pdo->prepare(
                "INSERT INTO patients (user_id, patient_code, full_name, date_of_birth, gender, phone, email, address)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $patientCode, $fullName, $dob ?: null, $gender, $phone, $email, $address]);

            $pdo->commit();
            log_activity($pdo, 'user', $userId, 'Self-registered account');

            flash('success', 'Account created successfully. You can now log in.');
            redirect(BASE_URL . '/auth/login.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong while creating your account. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="ambient-bg"><span></span><span></span><span></span></div>
<div class="center-screen">
  <div class="auth-shell" style="max-width:760px;">
    <div class="auth-form-side" style="grid-column:1/-1; padding:40px 44px;">
      <div class="eyebrow" style="color:var(--accent-dk); text-transform:uppercase; font-size:.72rem; letter-spacing:.14em; font-weight:700; margin-bottom:8px;">Patient Portal</div>
      <h2>Create your account</h2>
      <p class="hint" style="margin-bottom:20px;">This creates both your login and your patient record, so your reports can be linked to you.</p>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-error" data-autohide><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field-row">
          <div class="field">
            <label for="full_name">Full name *</label>
            <input type="text" id="full_name" name="full_name" required value="<?= e($old['full_name'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" required value="<?= e($old['username'] ?? '') ?>">
          </div>
        </div>
        <div class="field-row">
          <div class="field">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required value="<?= e($old['email'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= e($old['phone'] ?? '') ?>">
          </div>
        </div>
        <div class="field-row">
          <div class="field">
            <label for="dob">Date of birth</label>
            <input type="date" id="dob" name="dob" value="<?= e($old['dob'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
              <option value="">Select…</option>
              <option value="Male" <?= ($old['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= ($old['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other" <?= ($old['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" value="<?= e($old['address'] ?? '') ?>">
        </div>
        <div class="field-row">
          <div class="field">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="6">
          </div>
          <div class="field">
            <label for="confirm_password">Confirm password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
          </div>
        </div>
        <button type="submit" class="btn btn-accent btn-block">Create Account</button>
      </form>
      <div class="role-switch">Already registered? <a href="login.php">Log in</a></div>
    </div>
  </div>
</div>
</body>
</html>
