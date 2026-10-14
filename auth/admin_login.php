<?php
require_once __DIR__ . '/../config/config.php';

if (is_admin_logged_in()) redirect(BASE_URL . '/admin/dashboard.php');

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            log_activity($pdo, 'admin', $admin['admin_id'], 'Logged in');
            redirect(BASE_URL . '/admin/dashboard.php');
        } else {
            $error = 'Invalid admin username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="ambient-bg"><span></span><span></span><span></span></div>
<div class="center-screen">
  <div class="auth-shell">
    <div class="auth-visual">
      <div>
        <div class="eyebrow">Hospital Staff</div>
        <h1>Admin control center.</h1>
        <p>Manage patients, doctors, diagnostic panels, and generate finalized health reports.</p>
      </div>
      <svg class="pulse-line" viewBox="0 0 700 140" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 70 H150 L175 20 L210 120 L240 70 H320 L345 40 L365 100 L390 70 H700" />
      </svg>
      <p style="font-size:.8rem;">Every action is scoped to admin permissions and logged for audit purposes.</p>
    </div>
    <div class="auth-form-side">
      <h2>Admin Sign In</h2>
      <?php if ($error): ?><div class="alert alert-error" data-autohide><?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login to Dashboard</button>
      </form>
      <div class="role-switch">
        Not staff? <a href="login.php">Patient login</a> · <a href="<?= BASE_URL ?>/index.php">← Back home</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
