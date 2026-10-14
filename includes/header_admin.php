<?php
/** Expects: $pageTitle set by the including page. require_admin() must already have run. */
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admin') ?> · <?= e(APP_NAME) ?></title>
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
    <div class="section-label">Clinical</div>
    <nav>
      <a href="dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
      <a href="patients.php" class="<?= in_array($currentFile, ['patients.php','add_patient.php','edit_patient.php','patient_view.php']) ? 'active' : '' ?>">🧑‍🤝‍🧑 Patients</a>
      <a href="reports.php" class="<?= in_array($currentFile, ['reports.php','generate_report.php','view_report.php']) ? 'active' : '' ?>">📄 Health Reports</a>
      <a href="reports.php#monthly-download">⬇️ Monthly Download</a>
      <a href="doctors.php" class="<?= in_array($currentFile, ['doctors.php','add_doctor.php','edit_doctor.php']) ? 'active' : '' ?>">🩺 Doctors</a>
      <a href="references.php" class="<?= $currentFile === 'references.php' ? 'active' : '' ?>">📚 Medical References</a>
    </nav>
    <div class="section-label">Administration</div>
    <nav>
      <a href="users.php" class="<?= $currentFile === 'users.php' ? 'active' : '' ?>">👤 Manage Users</a>
      <a href="appointments.php" class="<?= $currentFile === 'appointments.php' ? 'active' : '' ?>">📅 Appointments</a>
      <a href="booking_report.php" class="<?= $currentFile === 'booking_report.php' ? 'active' : '' ?>">📈 Booking Report</a>
    </nav>
    <nav class="logout-link">
      <a href="<?= BASE_URL ?>/auth/logout.php?role=admin" data-confirm="Log out of the admin dashboard?">🚪 Logout</a>
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
        <div class="user-chip">
                <span class="chip-text"><strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong><small><?= e(ucfirst(str_replace('_', ' ', $_SESSION['admin_role'] ?? 'staff'))) ?></small></span>
                <div class="avatar avatar-ring"><?= e(strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1))) ?></div>
              </div>
      </div>
    </header>
    <main class="content fade-in">
