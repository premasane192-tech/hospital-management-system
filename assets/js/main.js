document.addEventListener('DOMContentLoaded', () => {

  // Auto-dismiss flash alerts after a few seconds
  document.querySelectorAll('.alert[data-autohide]').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s ease, transform .4s ease';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-6px)';
      setTimeout(() => el.remove(), 400);
    }, 4000);
  });

  // Confirm before any destructive action (delete buttons/links)
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      const msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // Live client-side filter for any table with data-search-target
  document.querySelectorAll('[data-search-input]').forEach(input => {
    const targetSelector = input.getAttribute('data-search-input');
    const rows = document.querySelectorAll(targetSelector + ' tbody tr');
    input.addEventListener('input', () => {
      const term = input.value.trim().toLowerCase();
      rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
      });
    });
  });

  // Print button (used for "PDF" download of reports — see README)
  document.querySelectorAll('[data-print]').forEach(btn => {
    btn.addEventListener('click', () => window.print());
  });

  // Sidebar active link highlight (in case not set server-side)
  const path = window.location.pathname.split('/').pop();
  document.querySelectorAll('.sidebar nav a').forEach(a => {
    if (a.getAttribute('href') && a.getAttribute('href').endsWith(path)) {
      a.classList.add('active');
    }
  });

  // Live status preview on health-test entry form
  document.querySelectorAll('[data-test-input]').forEach(input => {
    input.addEventListener('input', () => {
      const low = parseFloat(input.dataset.low);
      const high = parseFloat(input.dataset.high);
      const val = parseFloat(input.value);
      const badge = document.getElementById('status-' + input.dataset.testKey);
      if (!badge) return;
      if (isNaN(val)) { badge.textContent = '—'; badge.className = 'pill'; return; }
      let status = 'Normal', cls = 'pill pill-normal';
      if (val < low) { status = 'Low'; cls = 'pill pill-low'; }
      else if (val > high) { status = 'High'; cls = 'pill pill-high'; }
      badge.textContent = status;
      badge.className = cls;
    });
  });

  // Mobile sidebar toggle
  const toggle = document.getElementById('menuToggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const closeSidebar = () => {
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
  };
  if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      if (overlay) overlay.classList.toggle('open');
    });
  }
  if (overlay) overlay.addEventListener('click', closeSidebar);
  document.querySelectorAll('.sidebar nav a').forEach(a => a.addEventListener('click', closeSidebar));

  // ============ ANIMATIONS & DOWNLOAD HELPERS (added) ============
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1) Count-up numbers on dashboard stat cards
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10);
    if (isNaN(target) || reduceMotion) return;
    const duration = 900, start = performance.now();
    const tick = now => {
      const p = Math.min((now - start) / duration, 1);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(tick);
    };
    el.textContent = '0';
    requestAnimationFrame(tick);
  });

  // 2) Scroll-reveal for blocks that start below the fold
  const revealTargets = document.querySelectorAll('.content .section-title, .content .table-wrap, .content .report-doc');
  if ('IntersectionObserver' in window && !reduceMotion) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { threshold: 0.08 });
    revealTargets.forEach(el => {
      if (el.getBoundingClientRect().top > window.innerHeight) { el.classList.add('reveal'); io.observe(el); }
    });
  }

  // 3) Button ripple effect
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn');
    if (!btn || reduceMotion) return;
    const rect = btn.getBoundingClientRect();
    const dot = document.createElement('span');
    dot.className = 'ripple';
    dot.style.left = (e.clientX - rect.left) + 'px';
    dot.style.top = (e.clientY - rect.top) + 'px';
    btn.appendChild(dot);
    setTimeout(() => dot.remove(), 650);
  });

  // 4) Download feedback: spinner while the file is prepared (files don't navigate away)
  const busy = (btn, label) => {
    if (!btn) return;
    const original = btn.innerHTML;
    btn.classList.add('loading');
    btn.innerHTML = label;
    setTimeout(() => { btn.classList.remove('loading'); btn.innerHTML = original; }, 1800);
  };

  // Monthly download forms: PDF/print opens in a new tab, CSV downloads in place
  document.querySelectorAll('[data-download-form]').forEach(form => {
    const monthInput = form.querySelector('input[name="month"]');
    const allBox = form.querySelector('input[name="all"]');
    if (allBox && monthInput) {
      allBox.addEventListener('change', () => { monthInput.disabled = allBox.checked; });
    }
    form.addEventListener('submit', () => {
      const fmt = form.querySelector('[data-format]');
      form.target = (fmt && fmt.value === 'print') ? '_blank' : '';
      busy(form.querySelector('button[type="submit"]'), 'Preparing…');
    });
  });

  // One-click download links/cards (e.g. "Download this month")
  document.querySelectorAll('a[data-download]').forEach(a => {
    a.addEventListener('click', () => busy(a.classList.contains('btn') ? a : null, 'Preparing…'));
  });

  // ============ INTERFACE REFRESH v2 (added) ============

  // Dark / light theme (remembered in this browser)
  const themeBtn = document.getElementById('themeToggle');
  const root = document.documentElement;
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      const dark = root.getAttribute('data-theme') !== 'dark';
      if (dark) root.setAttribute('data-theme', 'dark'); else root.removeAttribute('data-theme');
      try { localStorage.setItem('cp-theme', dark ? 'dark' : 'light'); } catch (e) {}
    });
  }
  // Always print reports in light mode
  let wasDark = false;
  window.addEventListener('beforeprint', () => { wasDark = root.getAttribute('data-theme') === 'dark'; root.removeAttribute('data-theme'); });
  window.addEventListener('afterprint', () => { if (wasDark) root.setAttribute('data-theme', 'dark'); });

  // Time-of-day greeting, date and live clock (dashboards)
  const greetEl = document.querySelector('[data-greeting]');
  if (greetEl) {
    const h = new Date().getHours();
    greetEl.textContent = h < 12 ? 'Good morning' : (h < 17 ? 'Good afternoon' : 'Good evening');
  }
  const dateEl = document.querySelector('[data-date]');
  if (dateEl) dateEl.textContent = '📅 ' + new Date().toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
  const clockEl = document.querySelector('[data-clock]');
  if (clockEl) {
    const tickClock = () => { clockEl.textContent = '🕒 ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }); };
    tickClock(); setInterval(tickClock, 1000);
  }

  // Password show / hide buttons
  document.querySelectorAll('input[type="password"]').forEach(inp => {
    const field = inp.closest('.field');
    if (!field) return;
    field.classList.add('has-eye');
    const b = document.createElement('button');
    b.type = 'button'; b.className = 'eye-btn'; b.textContent = '👁️'; b.setAttribute('aria-label', 'Show or hide password');
    b.addEventListener('click', () => { inp.type = inp.type === 'password' ? 'text' : 'password'; b.textContent = inp.type === 'password' ? '👁️' : '🙈'; });
    field.appendChild(b);
  });

  // Subtle 3D tilt on stat cards (mouse devices only)
  if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
    document.querySelectorAll('.stat-card').forEach(card => {
      card.addEventListener('mousemove', e => {
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
        card.style.transform = 'perspective(600px) rotateY(' + (x * 7) + 'deg) rotateX(' + (-y * 7) + 'deg) translateY(-3px)';
      });
      card.addEventListener('mouseleave', () => { card.style.transform = ''; });
    });
  }

  // Smooth page-leave transition on normal internal links
  document.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('data-download') || a.hasAttribute('data-confirm') || a.hasAttribute('download')) return;
    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin || url.pathname === location.pathname && url.hash) return;
    if (url.pathname.includes('download_monthly') || reduceMotion) return;
    e.preventDefault();
    document.body.classList.add('leaving');
    setTimeout(() => { location.href = a.href; }, 170);
  });
  window.addEventListener('pageshow', () => document.body.classList.remove('leaving'));
});
