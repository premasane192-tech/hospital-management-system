<?php
$currentFile = basename($_SERVER['PHP_SELF']);

// Logged-in patient's identity, shown in the sidebar card + top bar
$hu = ['full_name' => $_SESSION['user_name'] ?? 'Patient', 'username' => '', 'email' => '', 'patient_code' => ''];
if (!empty($_SESSION['user_id']) && isset($pdo)) {
    $q = $pdo->prepare("SELECT u.full_name, u.username, u.email, p.patient_code
                        FROM users u LEFT JOIN patients p ON p.user_id = u.user_id
                        WHERE u.user_id = ?");
    $q->execute([$_SESSION['user_id']]);
    if ($row = $q->fetch()) $hu = array_merge($hu, array_filter($row, fn($v) => $v !== null));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'My Account') ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script>try{if(localStorage.getItem('cp-theme')==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
</head>
<body>
<div class="ambient-bg"><span></span><span></span><span></span></div>
<div class="app-shell">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <aside class="sidebar">
    <div class="brand">
      <svg class="pulse-mini" viewBox="0 0 64 22" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 11 H14 L18 3 L24 19 L29 11 H36 L40 6 L44 16 L48 11 H64" />
      </svg>
      <strong>Care Plus</strong>
    </div>
    <a href="profile.php" class="side-user">
      <div class="avatar avatar-ring"><?= e(strtoupper(substr($hu['full_name'], 0, 1))) ?></div>
      <div class="side-user-text">
        <strong><?= e($hu['full_name']) ?></strong>
        <small><?= e($hu['patient_code'] ?: ('@' . $hu['username'])) ?></small>
        <small><?= e($hu['email']) ?></small>
      </div>
    </a>
    <div class="section-label">My Account</div>
    <nav>
      <a href="dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a>
      <a href="profile.php" class="<?= $currentFile === 'profile.php' ? 'active' : '' ?>">🧾 My Profile</a>
      <a href="reports.php" class="<?= in_array($currentFile, ['reports.php','view_report.php']) ? 'active' : '' ?>">📄 My Reports</a>
    </nav>
    <nav class="logout-link">
      <a href="<?= BASE_URL ?>/auth/logout.php?role=user" data-confirm="Log out of your account?">🚪 Logout</a>
    </nav>
  </aside>

  <div>
    <header class="topbar">
      <div class="left-group">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">☰</button>
        <h2><?= e($pageTitle ?? 'Dashboard') ?></h2>
      </div>
      <div class="topbar-actions">
        <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch light/dark theme" title="Switch theme"><span class="ti-sun">☀️</span><span class="ti-moon">🌙</span></button>
        <a href="profile.php" class="user-chip">
                <span class="chip-text"><strong><?= e($hu['full_name']) ?></strong><small><?= e($hu['patient_code'] ?: ('@' . $hu['username'])) ?></small></span>
                <div class="avatar avatar-ring"><?= e(strtoupper(substr($hu['full_name'], 0, 1))) ?></div>
              </a>
      </div>
    </header>
    <main class="content fade-in">
