<?php require_once __DIR__ . '/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(APP_NAME) ?> — Digital Health Reports</title>
<meta name="description" content="Register patients, run diagnostic panels against reference ranges and generate doctor-reviewed health reports with secure, role-based access.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/landing.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/hero2.css">
<script>try{if(localStorage.getItem('cp-theme')==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
</head>
<body>
<div class="bg-orbs"><i></i><i></i><i></i></div>
<div class="bg-grid"></div>

<!-- ============ NAVIGATION ============ -->
<header class="nav" id="nav">
  <div class="wrap">
    <a href="#top" class="brand">
      <svg viewBox="0 0 64 22" xmlns="http://www.w3.org/2000/svg"><path d="M0 11 H14 L18 3 L24 19 L29 11 H36 L40 6 L44 16 L48 11 H64"/></svg>
      Care Plus
    </a>
    <nav class="nav-links" id="navLinks" aria-label="Main">
      <a href="#features">Features</a>
      <a href="#how">How it works</a>
      <a href="<?= BASE_URL ?>/auth/admin_login.php">Staff login</a>
      <a href="<?= BASE_URL ?>/book/index.php" class="bmc-link">🩺 Book My Care</a>
    </nav>
    <div class="nav-cta">
      <button class="icon-btn" id="themeBtn" aria-label="Switch light/dark theme"><span class="sun">☀️</span><span class="moon">🌙</span></button>
      <a class="btn btn-ghost btn-sm hide-m" href="<?= BASE_URL ?>/auth/login.php">Login</a>
      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/auth/register.php">Register <span class="arr">→</span></a>
      <button class="icon-btn burger" id="burger" aria-label="Open menu">☰</button>
    </div>
  </div>
</header>

<main id="top">
<!-- ============ HERO ============ -->
<section class="hero hero2">
  <div class="wrap">
  <div class="h2-copy">
    <span class="h2-badge h2-rise" style="animation-delay:.1s"><b>Digital</b> Secure health records &amp; reports</span>
    <h1 class="h2-rise" style="animation-delay:.22s">Every vital sign,<br>one secure <span class="h2-rot" id="rot"><span class="on">vault.</span><span>history.</span><span>profile.</span><span>story.</span></span>
      <i class="h2-under"></i></h1>
    <p class="h2-lead h2-rise" style="animation-delay:.36s">Book a doctor's appointment online, get your visit time instantly, and simply report to the hospital at that time. View your health reports anytime — private to you.</p>
    <div class="h2-cta h2-rise" style="animation-delay:.48s">
      <a class="h2-btn h2-btn-p" href="<?= BASE_URL ?>/auth/register.php">Get Started <span class="arr">→</span></a>
      <a class="h2-btn h2-btn-g" href="<?= BASE_URL ?>/book/index.php">🩺 Book My Care <span class="arr">→</span></a>
    </div>
    <div class="h2-trust h2-rise" style="animation-delay:.6s"><span>Role-based access</span><span>10 auto-analysed tests</span><span>Print / PDF ready</span></div>
  </div>

  <div class="h2-stage" id="stage">
    <div class="h2-ecg">
      <div class="h2-ecg-head">
        <span class="h2-live"></span> LIVE &middot; LEAD II
        <span class="h2-ecg-bpm"><svg class="h2-heart" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg><b id="bpm">73</b> BPM</span>
      </div>
      <div class="h2-screen" role="img" aria-label="Animated heartbeat trace">
        <div class="h2-track"><svg viewBox="0 0 2400 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 110 H40 q12 -22 24 0 H80 l8 10 l12 -100 l14 160 l10 -70 H170 q26 -42 52 0 H300 H340 q12 -22 24 0 H380 l8 10 l12 -100 l14 160 l10 -70 H470 q26 -42 52 0 H600 H640 q12 -22 24 0 H680 l8 10 l12 -100 l14 160 l10 -70 H770 q26 -42 52 0 H900 H940 q12 -22 24 0 H980 l8 10 l12 -100 l14 160 l10 -70 H1070 q26 -42 52 0 H1200 H1240 q12 -22 24 0 H1280 l8 10 l12 -100 l14 160 l10 -70 H1370 q26 -42 52 0 H1500 H1540 q12 -22 24 0 H1580 l8 10 l12 -100 l14 160 l10 -70 H1670 q26 -42 52 0 H1800 H1840 q12 -22 24 0 H1880 l8 10 l12 -100 l14 160 l10 -70 H1970 q26 -42 52 0 H2100 H2140 q12 -22 24 0 H2180 l8 10 l12 -100 l14 160 l10 -70 H2270 q26 -42 52 0 H2400"/></svg></div>
      </div>
      <div class="h2-vitals">
        <div class="h2-vital"><small>Blood pressure</small><strong>118/78<i>mmHg</i></strong><div class="h2-bars"><span style="--i:0"></span><span style="--i:1"></span><span style="--i:2"></span><span style="--i:3"></span><span style="--i:4"></span><span style="--i:5"></span><span style="--i:6"></span><span style="--i:7"></span></div></div>
        <div class="h2-vital h2-v2"><small>SpO&#8322;</small><strong>98<i>%</i></strong><div class="h2-bars"><span style="--i:0"></span><span style="--i:1"></span><span style="--i:2"></span><span style="--i:3"></span><span style="--i:4"></span><span style="--i:5"></span><span style="--i:6"></span><span style="--i:7"></span></div></div>
        <div class="h2-vital h2-v3"><small>Temp</small><strong>98.4<i>&deg;F</i></strong><div class="h2-bars"><span style="--i:0"></span><span style="--i:1"></span><span style="--i:2"></span><span style="--i:3"></span><span style="--i:4"></span><span style="--i:5"></span><span style="--i:6"></span><span style="--i:7"></span></div></div>
      </div>
    </div>
    <div class="h2-chip h2-c1"><span>🩺</span> Doctor reviewed</div>
    <div class="h2-chip h2-c2"><span>🔒</span> Your data, only yours</div>
    <div class="h2-chip h2-c3"><span>🧪</span> 10 tests analysed</div>
    <div class="h2-toast" id="toast"></div>
  </div>
  </div>
</section>

<!-- ============ STATS ============ -->
<section class="stats">
  <div class="wrap rv">
    <div class="stats-grid">
      <div class="stat"><b data-to="10">0</b><span>Diagnostic panels</span></div>
      <div class="stat"><b data-to="24" data-suffix="/7">0</b><span>Access to your reports</span></div>
      <div class="stat"><b data-to="100" data-suffix="%">0</b><span>Private to each patient</span></div>
    </div>
  </div>
</section>

<!-- ============ FEATURES ============ -->
<section class="sec" id="features">
  <div class="wrap">
    <div class="sec-head rv"><span class="eyebrow">Features</span><h2>Everything a clinic needs, beautifully simple</h2><p>From the front desk to the doctor's notes, Care Plus keeps the whole reporting workflow in one place.</p></div>
    <div class="features">
      <article class="f-card rv" style="--d:0"><div class="f-ico">🧑‍🤝‍🧑</div><h3>Patient registration</h3><p>Register walk-ins or link them to a login account, with unique patient codes generated automatically.</p></article>
      <article class="f-card rv" style="--d:1"><div class="f-ico">🧪</div><h3>Smart test analysis</h3><p>Ten diagnostic panels are compared with configurable reference ranges and flagged Low, Normal or High instantly.</p></article>
      <article class="f-card rv" style="--d:2"><div class="f-ico">🩺</div><h3>Doctor-reviewed reports</h3><p>Attach the doctor's notes, recommendations and follow-up advice to every final report.</p></article>
      <article class="f-card rv" style="--d:3"><div class="f-ico">📚</div><h3>Medical references</h3><p>Normal ranges and plain-language explanations are configurable, so every report stays accurate and easy to understand.</p></article>
      <article class="f-card rv" style="--d:4"><div class="f-ico">🔒</div><h3>Private by design</h3><p>Separate admin and patient logins with ownership checks — patients can never open someone else's report.</p></article>
      <article class="f-card rv" style="--d:5"><div class="f-ico">📄</div><h3>Print, PDF &amp; monthly exports</h3><p>Print or save any report as PDF, and let administrators download complete monthly reports.</p></article>
    </div>
  </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="sec" id="how">
  <div class="wrap">
    <div class="sec-head rv"><span class="eyebrow">How it works</span><h2>From registration to report in three steps</h2></div>
    <div class="steps">
      <div class="step rv" style="--d:0"><div class="n">1</div><h3>Register</h3><p>Staff add the patient, or the patient creates their own account.</p></div>
      <div class="step rv" style="--d:1"><div class="n">2</div><h3>Test &amp; analyse</h3><p>Enter test values and see each one checked against its normal range.</p></div>
      <div class="step rv" style="--d:2"><div class="n">3</div><h3>Review &amp; share</h3><p>The doctor's notes are added and the patient views or prints the report.</p></div>
    </div>
  </div>
</section>

<!-- ============ CTA ============ -->
<section class="cta">
  <div class="wrap">
    <div class="cta-box rv">
      <h2>Ready to take your health records online?</h2>
      <p>Create your free patient account in a minute, or sign in to continue.</p>
      <div class="cta-row">
        <a class="btn btn-white" href="<?= BASE_URL ?>/auth/register.php">Get Started <span class="arr">→</span></a>
        <a class="btn btn-outline-w" href="<?= BASE_URL ?>/auth/login.php">Login</a>
      </div>
      <svg viewBox="0 0 1600 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 35 H300 L330 8 L370 62 L400 35 H700 L730 15 L760 55 L790 35 H1100 L1130 8 L1170 62 L1200 35 H1600"/></svg>
    </div>
  </div>
</section>
</main>

<footer>
  <div class="wrap foot">
    <div>© <?= date('Y') ?> <?= e(HOSPITAL_NAME) ?> · Digital Health Report Generator</div>
    <nav><a href="#features">Features</a><a href="<?= BASE_URL ?>/auth/admin_login.php">Staff login</a><a href="<?= BASE_URL ?>/book/index.php">Book My Care</a></nav>
  </div>
</footer>

<button class="to-top" id="toTop" aria-label="Back to top">↑</button>
<script src="<?= BASE_URL ?>/assets/js/landing.js"></script>
<script src="<?= BASE_URL ?>/assets/js/hero2.js"></script>
</body>
</html>
