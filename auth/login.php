<?php
require_once __DIR__ . '/../config/config.php';

if (is_user_logged_in()) redirect(BASE_URL . '/user/dashboard.php');

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username/email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            log_activity($pdo, 'user', $user['user_id'], 'Logged in');
            redirect(BASE_URL . '/user/dashboard.php');
        } elseif ($user && $user['status'] !== 'active') {
            $error = 'Your account has been disabled. Please contact the hospital administrator.';
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Login · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="ambient-bg"><span></span><span></span><span></span></div>
<div class="center-screen">
  <div class="auth-shell">
    <div class="auth-visual">
      <div>
        <div class="eyebrow">Patient Portal</div>
        <h1>Your health record, always in reach.</h1>
        <p>View your latest results, understand what each number means, and download your report as a PDF.</p>
      </div>
      <svg class="pulse-line" viewBox="0 0 700 140" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 70 H150 L175 20 L210 120 L240 70 H320 L345 40 L365 100 L390 70 H700" />
      </svg>
      <p style="font-size:.8rem;">You will only ever see reports and information linked to your own account.</p>
    </div>
    <div class="auth-form-side">
      <h2>Patient Sign In</h2>
      <?php if ($error): ?><div class="alert alert-error" data-autohide><?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field">
          <label for="username">Username or email</label>
          <input type="text" id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-accent btn-block">Login</button>
      </form>
      <div class="role-switch">
        No account? <a href="register.php">Register</a> · <a href="admin_login.php">Staff login</a> · <a href="<?= BASE_URL ?>/index.php">← Home</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
