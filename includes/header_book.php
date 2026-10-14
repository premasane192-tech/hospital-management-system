<?php /** Simple public page header for Book My Care (no login, no sidebar). */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Book My Care') ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<style>
  .bmc-top{display:flex;align-items:center;justify-content:space-between;max-width:860px;margin:0 auto;padding:18px 20px;}
  .bmc-top .brand{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-size:1.4rem;color:var(--primary-dk);}
  .bmc-top .brand a{color:inherit;}
  .bmc-top .right{display:flex;align-items:center;gap:14px;font-size:.9rem;}
  .bmc-wrap{max-width:860px;margin:0 auto;padding:0 20px 48px;}
  .bmc-hero{text-align:center;margin:8px 0 26px;}
  .bmc-hero h1{margin:0 0 6px;font-size:1.9rem;}
  .bmc-hero p{margin:0;color:var(--muted,#5b6b7b);}
</style>
<script>try{if(localStorage.getItem('cp-theme')==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
</head>
<body>
<div class="ambient-bg"><span></span><span></span><span></span></div>
<header class="bmc-top">
  <div class="brand">
    <svg class="pulse-mini" viewBox="0 0 64 22" width="44" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M0 11 H14 L18 3 L24 19 L29 11 H36 L40 6 L44 16 L48 11 H64"/></svg>
    <a href="<?= BASE_URL ?>/index.php">Care Plus</a>
  </div>
  <div class="right">
    <a href="<?= BASE_URL ?>/index.php">← Home</a>
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch theme"><span class="ti-sun">☀️</span><span class="ti-moon">🌙</span></button>
  </div>
</header>
<main class="bmc-wrap fade-in">
<div class="bmc-hero">
  <h1>Book My Care</h1>
  <p>Pick a doctor and a day. We'll give you your time — just report to the hospital.<br><small>Booking hours: 9:00 AM – 12:00 PM and 2:00 PM – 7:00 PM, Monday–Saturday.</small></p>
</div>
