(function () {
  const root = document.documentElement;
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];

  // Theme toggle (shares the same setting as the dashboards)
  $('#themeBtn').addEventListener('click', () => {
    const dark = root.getAttribute('data-theme') !== 'dark';
    dark ? root.setAttribute('data-theme', 'dark') : root.removeAttribute('data-theme');
    try { localStorage.setItem('cp-theme', dark ? 'dark' : 'light'); } catch (e) {}
  });

  // Sticky nav style + back-to-top + active link
  const nav = $('#nav'), top = $('#toTop');
  const links = $$('.nav-links a[href^="#"]');
  const sections = links.map(a => $(a.getAttribute('href'))).filter(Boolean);
  const onScroll = () => {
    const y = window.scrollY;
    nav.classList.toggle('scrolled', y > 24);
    top.classList.toggle('show', y > 600);
    let cur = null;
    sections.forEach(s => { if (s.getBoundingClientRect().top < 140) cur = s.id; });
    links.forEach(a => a.classList.toggle('on', a.getAttribute('href') === '#' + cur));
  };
  window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  top.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  // Mobile menu
  const burger = $('#burger'), menu = $('#navLinks');
  burger.addEventListener('click', () => menu.classList.toggle('open'));
  links.forEach(a => a.addEventListener('click', () => menu.classList.remove('open')));

  // Scroll reveal
  const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
  }), { threshold: 0.14, rootMargin: '0px 0px -40px 0px' });
  $$('.rv').forEach(el => io.observe(el));

  // Count-up numbers when visible
  const cio = new IntersectionObserver(es => es.forEach(e => {
    if (!e.isIntersecting) return;
    const el = e.target, end = parseFloat(el.dataset.to), suffix = el.dataset.suffix || '';
    cio.unobserve(el);
    if (reduce) { el.textContent = end + suffix; return; }
    const t0 = performance.now(), dur = 1400;
    const step = t => { const p = Math.min((t - t0) / dur, 1); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))) + suffix; if (p < 1) requestAnimationFrame(step); };
    requestAnimationFrame(step);
  }), { threshold: 0.6 });
  $$('[data-to]').forEach(el => cio.observe(el));

  // Cursor spotlight on feature cards
  $$('.f-card').forEach(c => c.addEventListener('mousemove', e => {
    const r = c.getBoundingClientRect();
    c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
    c.style.setProperty('--my', (e.clientY - r.top) + 'px');
  }));

  // 3D tilt on the hero report card
  const stage = $('#stage'), card = $('#tilt');
  if (stage && card && !reduce && matchMedia('(hover:hover)').matches && innerWidth > 960) {
    stage.addEventListener('mousemove', e => {
      const r = stage.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      card.style.transform = `perspective(900px) rotateY(${x * 10}deg) rotateX(${-y * 10}deg)`;
    });
    stage.addEventListener('mouseleave', () => card.style.transform = '');
  }

  // Live BPM readout in the hero animation
  const bpm = $('#bpm');
  if (bpm && !reduce) setInterval(() => { bpm.textContent = 72 + Math.floor(Math.random() * 9); }, 1250);

  // Button ripple
  document.addEventListener('click', e => {
    const b = e.target.closest('.btn'); if (!b || reduce) return;
    const r = b.getBoundingClientRect(), d = document.createElement('span');
    d.className = 'ripple'; d.style.left = (e.clientX - r.left) + 'px'; d.style.top = (e.clientY - r.top) + 'px';
    b.appendChild(d); setTimeout(() => d.remove(), 650);
  });
})();
