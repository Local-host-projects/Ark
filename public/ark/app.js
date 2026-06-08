(function(){
  const body = document.body;
  const themeKey = 'ark_theme';
  const applyTheme = (theme) => {
    if (theme === 'dark') body.classList.add('dark');
    else body.classList.remove('dark');
  };
  applyTheme(localStorage.getItem(themeKey) || 'light');

  // ── Click Handler ──────────────────────────────────────────
  document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-theme-toggle]');
    if (toggle) {
      const next = body.classList.contains('dark') ? 'light' : 'dark';
      localStorage.setItem(themeKey, next);
      applyTheme(next);
    }

    // FIXED: Sidebar open with proper display handling
    const openBtn = e.target.closest('[data-sidebar-open]');
    if (openBtn) {
      e.preventDefault();
      e.stopPropagation();
      const sidebar = document.getElementById('sidebar');
      const backdrop = document.getElementById('sidebarBackdrop');
      if (sidebar) sidebar.classList.add('open');
      if (backdrop) {
        backdrop.style.display = 'block';
        requestAnimationFrame(() => backdrop.classList.add('show'));
      }
    }

    // FIXED: Backdrop click to close with proper cleanup
    const backdropClick = e.target.closest('#sidebarBackdrop');
    if (backdropClick) {
      e.preventDefault();
      e.stopPropagation();
      const sidebar = document.getElementById('sidebar');
      const bd = document.getElementById('sidebarBackdrop');
      if (sidebar) sidebar.classList.remove('open');
      if (bd) {
        bd.classList.remove('show');
        setTimeout(() => { bd.style.display = 'none'; }, 300);
      }
    }
  });

  // FIXED: Escape key to close sidebar
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const sidebar = document.getElementById('sidebar');
      const bd = document.getElementById('sidebarBackdrop');
      if (sidebar) sidebar.classList.remove('open');
      if (bd) {
        bd.classList.remove('show');
        setTimeout(() => { bd.style.display = 'none'; }, 300);
      }
    }
  });

  // FIXED: Mobile swipe to close sidebar
  let touchStartX = 0;
  document.addEventListener('touchstart', (e) => {
    touchStartX = e.touches[0].clientX;
  }, { passive: true });

  document.addEventListener('touchend', (e) => {
    const touchEndX = e.changedTouches[0].clientX;
    const diff = touchStartX - touchEndX;
    const sidebar = document.getElementById('sidebar');
    const bd = document.getElementById('sidebarBackdrop');
    
    // Swipe left on sidebar to close
    if (sidebar?.classList.contains('open') && diff > 50 && touchStartX < 300) {
      sidebar.classList.remove('open');
      if (bd) {
        bd.classList.remove('show');
        setTimeout(() => { bd.style.display = 'none'; }, 300);
      }
    }
  }, { passive: true });

  // ── Countdown Timer ────────────────────────────────────────
  document.querySelectorAll('[data-countdown]').forEach((el) => {
    const run = () => {
      const target = new Date(el.dataset.countdown);
      const diff = target - new Date();
      if (isNaN(target.getTime())) return;
      if (diff <= 0) { 
        const span = el.querySelector('span') || el;
        span.textContent = 'Due now'; 
        return; 
      }
      const m = Math.floor(diff/60000);
      const h = Math.floor(m/60);
      const mm = m % 60;
      const text = h > 0 ? `Next in ${h}h ${mm}m` : `Next in ${mm}m`;
      const span = el.querySelector('span') || el;
      span.textContent = text;
    };
    run(); 
    setInterval(run, 30000);
  });

  // ── Auth Tabs ──────────────────────────────────────────────
  document.querySelectorAll('[data-auth-tab]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.authTab;
      document.querySelectorAll('[data-auth-tab]').forEach((t) => t.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('[data-auth-panel]').forEach((panel) => {
        panel.classList.toggle('active', panel.dataset.authPanel === tab);
      });
    });
  });

  // ── Audio Player ───────────────────────────────────────────
  const audio = document.getElementById('spaceAudio');
  const visualizer = document.getElementById('audioVisualizer');
  const lines = [...document.querySelectorAll('[data-audio-line]')];
  
  if (audio && lines.length) {
    let current = 0;
    
    const playIndex = (index) => {
      current = index;
      lines.forEach((line, i) => line.classList.toggle('active', i === index));
      audio.src = lines[index].dataset.src;
      audio.play().catch(() => {});
      if (visualizer) visualizer.classList.remove('paused');
    };
    
    lines.forEach((line, idx) => line.addEventListener('click', () => playIndex(idx)));
    
    audio.addEventListener('play', () => {
      if (visualizer) visualizer.classList.remove('paused');
    });
    
    audio.addEventListener('pause', () => {
      if (visualizer) visualizer.classList.add('paused');
    });
    
    audio.addEventListener('ended', () => {
      if (current + 1 < lines.length) {
        playIndex(current + 1);
      } else {
        lines.forEach((line) => line.classList.remove('active'));
        if (visualizer) visualizer.classList.add('paused');
      }
    });
  }

  // FIXED: Prevent bottom nav from capturing all touches
  const bottomNav = document.querySelector('.bottom-nav');
  if (bottomNav) {
    bottomNav.addEventListener('touchstart', (e) => {
      if (!e.target.closest('.bottom-nav-item')) {
        return;
      }
    }, { passive: true });
  }
})();