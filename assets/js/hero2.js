(function () {
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Rotating headline word
  var words = document.querySelectorAll('#rot span'), wi = 0;
  if (words.length > 1 && !reduce) setInterval(function () {
    var cur = words[wi]; wi = (wi + 1) % words.length; var nx = words[wi];
    cur.classList.remove('on'); cur.classList.add('out');
    nx.style.transition = 'none'; nx.classList.remove('out'); nx.style.transform = 'translateY(100%)'; nx.offsetHeight;
    nx.style.transition = ''; nx.style.transform = ''; nx.classList.add('on');
    setTimeout(function () { cur.classList.remove('out'); }, 700);
  }, 2400);

  // Pop-up messages
  var toast = document.getElementById('toast');
  if (toast && !reduce) {
    var msgs = ['\u2705 Appointment booked \u00b7 09:00 AM', '\ud83e\uddea Blood panel analysed', '\ud83d\udcc4 Report ready to print', '\ud83e\ude7a Doctor notes added'], ti = 0;
    var show = function () {
      toast.textContent = msgs[ti++ % msgs.length]; toast.classList.add('show');
      setTimeout(function () { toast.classList.remove('show'); }, 2300);
    };
    setTimeout(function () { show(); setInterval(show, 3400); }, 1400);
  }

  // Gentle mouse tilt of the scene
  var st = document.getElementById('stage');
  if (st && !reduce && matchMedia('(hover:hover)').matches && innerWidth > 960) {
    document.addEventListener('mousemove', function (e) {
      var x = e.clientX / innerWidth - .5, y = e.clientY / innerHeight - .5;
      st.style.transform = 'perspective(900px) rotateY(' + (x * 8) + 'deg) rotateX(' + (-y * 8) + 'deg)';
    });
  }
})();
