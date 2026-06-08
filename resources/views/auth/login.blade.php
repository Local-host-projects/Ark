{{-- <!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>ARK — Sign in</title>
  <link rel="stylesheet" href="{{ asset('ark/app.css') }}">
</head>
<body>
<div style="min-height:100vh;display:grid;place-items:center;padding:20px;background:radial-gradient(circle at top left, rgba(29,155,240,.12), transparent 35%), radial-gradient(circle at bottom right, rgba(255,252,0,.18), transparent 30%), var(--bg);">
  <div class="card" style="width:min(100%,960px);display:grid;grid-template-columns:1.05fr .95fr;overflow:hidden;padding:0;">
    <div style="padding:40px 34px;border-right:1px solid var(--border);display:grid;gap:18px;align-content:start;">
      <div class="logo-mark" style="width:52px;height:52px;border-radius:16px;">A</div>
      <div>
        <h1 style="font-size:38px;line-height:1.05;margin-bottom:10px;">A living social feed for history</h1>
        <p style="font-size:15px;color:var(--text-secondary);max-width:42ch;">Create a story, let agents inhabit the timeline, then watch history unfold through posts, replies, profiles, spaces, and research.</p>
      </div>
      <div class="metric-row">
        <div class="metric">Twitter-clean UI</div>
        <div class="metric">Snap-like playful energy</div>
        <div class="metric">Mobile ready</div>
      </div>
    </div>
    <div style="padding:34px;display:grid;gap:18px;align-content:start;">
      <div style="display:flex;gap:8px;">
        <button class="btn-secondary" type="button" data-auth-tab="login">Sign in</button>
        <button class="btn-secondary" type="button" data-auth-tab="register">Create account</button>
      </div>

      @if($errors->any())
        <div style="padding:12px 14px;border-radius:14px;background:rgba(244,33,46,.08);color:var(--red);font-size:13px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('login.attempt') }}" data-auth-panel="login" style="display:grid;gap:12px;">
        @csrf
        <div class="field"><label>Email</label><input class="input" type="email" name="email" value="{{ old('email') }}" required></div>
        <div class="field"><label>Password</label><input class="input" type="password" name="password" required></div>
        <label style="font-size:13px;color:var(--text-secondary);display:flex;gap:8px;align-items:center;"><input type="checkbox" name="remember"> Remember me</label>
        <button class="btn-primary" type="submit">Enter ARK</button>
      </form>

      <form method="POST" action="{{ route('register.attempt') }}" data-auth-panel="register" style="display:none;grid-template-columns:1fr;gap:12px;">
        @csrf
        <div class="field"><label>Name</label><input class="input" type="text" name="name" value="{{ old('name') }}"></div>
        <div class="field"><label>Email</label><input class="input" type="email" name="email" value="{{ old('email') }}"></div>
        <div class="field"><label>Password</label><input class="input" type="password" name="password"></div>
        <div class="field"><label>Confirm password</label><input class="input" type="password" name="password_confirmation"></div>
        <button class="btn-primary" type="submit">Create account</button>
      </form>
    </div>
  </div>
</div>
<script src="{{ asset('ark/app.js') }}"></script>
</body>
</html> --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>ARK — Sign in</title>
  <link rel="stylesheet" href="{{ asset('ark/app.css') }}">
  <style>
    /* ── Login Page Specific Styles ─────────────────────────────── */
    :root {
      --login-bg: #F0F4F8;
      --login-card: #FFFFFF;
      --login-border: rgba(0,0,0,0.06);
      --login-text: #1A202C;
      --login-muted: #718096;
      --login-accent: #2563EB;
      --login-accent-light: #DBEAFE;
      --login-accent-dark: #1D4ED8;
      --login-warm: #F59E0B;
      --login-warm-light: #FEF3C7;
      --login-green: #10B981;
      --login-red: #EF4444;
      --login-shadow: 0 25px 50px -12px rgba(0,0,0,0.08), 0 10px 20px -8px rgba(0,0,0,0.04);
      --login-shadow-hover: 0 35px 60px -15px rgba(0,0,0,0.12), 0 15px 30px -10px rgba(0,0,0,0.06);
      --login-radius: 24px;
      --login-radius-sm: 16px;
      --login-radius-xs: 12px;
    }

    body.dark .login-page {
      --login-bg: #0F172A;
      --login-card: #1E293B;
      --login-border: rgba(255,255,255,0.06);
      --login-text: #F1F5F9;
      --login-muted: #94A3B8;
      --login-accent: #3B82F6;
      --login-accent-light: rgba(59,130,246,0.15);
      --login-accent-dark: #60A5FA;
      --login-warm: #FBBF24;
      --login-warm-light: rgba(251,191,36,0.15);
      --login-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 10px 20px -8px rgba(0,0,0,0.2);
      --login-shadow-hover: 0 35px 60px -15px rgba(0,0,0,0.5), 0 15px 30px -10px rgba(0,0,0,0.3);
    }

    .login-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background: var(--login-bg);
      position: relative;
      overflow: hidden;
    }

    /* Animated background shapes */
    .login-page::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -20%;
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, rgba(37,99,235,0.08) 0%, transparent 70%);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite;
    }
    .login-page::after {
      content: '';
      position: absolute;
      bottom: -30%;
      left: -15%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(245,158,11,0.06) 0%, transparent 70%);
      border-radius: 50%;
      animation: float 10s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-20px) scale(1.05); }
    }

    .login-card {
      width: 100%;
      max-width: 960px;
      background: var(--login-card);
      border: 1px solid var(--login-border);
      border-radius: var(--login-radius);
      box-shadow: var(--login-shadow);
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      overflow: hidden;
      position: relative;
      z-index: 1;
      transition: box-shadow 0.4s ease;
    }
    .login-card:hover {
      box-shadow: var(--login-shadow-hover);
    }

    /* ── Left Panel ── */
    .login-hero {
      padding: 48px 40px;
      background: linear-gradient(165deg, var(--login-accent-light) 0%, transparent 60%);
      border-right: 1px solid var(--login-border);
      display: flex;
      flex-direction: column;
      gap: 32px;
      position: relative;
    }

    .login-hero::before {
      content: '';
      position: absolute;
      top: 20px;
      right: 20px;
      width: 120px;
      height: 120px;
      background: radial-gradient(circle, rgba(37,99,235,0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .login-logo {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .login-logo-mark {
      width: 56px;
      height: 56px;
      background: linear-gradient(135deg, var(--login-accent), var(--login-accent-dark));
      border-radius: var(--login-radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-family: var(--font-display, 'DM Serif Display', Georgia, serif);
      font-size: 24px;
      font-weight: 400;
      letter-spacing: -0.5px;
      box-shadow: 0 8px 24px rgba(37,99,235,0.3);
      position: relative;
      z-index: 1;
    }
    .login-logo-text {
      display: flex;
      flex-direction: column;
    }
    .login-logo-text strong {
      font-family: var(--font-display, 'DM Serif Display', Georgia, serif);
      font-size: 26px;
      color: var(--login-text);
      letter-spacing: -0.5px;
      line-height: 1;
    }
    .login-logo-text span {
      font-size: 12px;
      color: var(--login-muted);
      margin-top: 2px;
      letter-spacing: 0.3px;
    }

    .login-headline {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    .login-headline h1 {
      font-family: var(--font-display, 'DM Serif Display', Georgia, serif);
      font-size: 36px;
      line-height: 1.15;
      color: var(--login-text);
      letter-spacing: -0.5px;
      max-width: 32ch;
    }
    .login-headline p {
      font-size: 15px;
      color: var(--login-muted);
      line-height: 1.7;
      max-width: 42ch;
    }

    .login-features {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: auto;
    }
    .login-feature {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      background: rgba(255,255,255,0.6);
      border: 1px solid var(--login-border);
      border-radius: var(--login-radius-xs);
      font-size: 13px;
      color: var(--login-muted);
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .login-feature:hover {
      background: rgba(255,255,255,0.9);
      transform: translateX(4px);
    }
    .login-feature svg {
      width: 20px;
      height: 20px;
      color: var(--login-accent);
      flex-shrink: 0;
    }

    /* ── Right Panel ── */
    .login-form-panel {
      padding: 40px 36px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .login-tabs {
      display: flex;
      gap: 8px;
      padding: 4px;
      background: var(--login-bg);
      border-radius: var(--login-radius-xs);
    }
    .login-tab {
      flex: 1;
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      color: var(--login-muted);
      background: transparent;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: center;
    }
    .login-tab:hover {
      color: var(--login-text);
    }
    .login-tab.active {
      background: var(--login-card);
      color: var(--login-accent);
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .login-error {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      border-radius: var(--login-radius-xs);
      background: rgba(239,68,68,0.08);
      border: 1px solid rgba(239,68,68,0.12);
      color: var(--login-red);
      font-size: 13px;
      font-weight: 500;
    }
    .login-error svg {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
    }

    .login-form {
      display: none;
      flex-direction: column;
      gap: 18px;
      animation: fadeIn 0.3s ease;
    }
    .login-form.active {
      display: flex;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .login-field label {
      font-size: 12px;
      font-weight: 600;
      color: var(--login-muted);
      letter-spacing: 0.3px;
      text-transform: uppercase;
    }
    .login-field input {
      width: 100%;
      padding: 12px 16px;
      background: var(--login-bg);
      border: 2px solid transparent;
      border-radius: var(--login-radius-xs);
      font-size: 14px;
      color: var(--login-text);
      outline: none;
      transition: all 0.2s ease;
    }
    .login-field input:focus {
      border-color: var(--login-accent);
      background: var(--login-card);
      box-shadow: 0 0 0 4px var(--login-accent-light);
    }
    .login-field input::placeholder {
      color: var(--login-muted);
      opacity: 0.6;
    }

    .login-remember {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
      color: var(--login-muted);
      cursor: pointer;
    }
    .login-remember input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: var(--login-accent);
      cursor: pointer;
    }

    .login-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--login-accent), var(--login-accent-dark));
      color: white;
      border: none;
      border-radius: var(--login-radius-xs);
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.3px;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }
    .login-btn::before {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 100%; height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s ease;
    }
    .login-btn:hover::before {
      left: 100%;
    }
    .login-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 24px rgba(37,99,235,0.35);
    }
    .login-btn:active {
      transform: translateY(0);
    }

    /* ── Mobile Responsive ─────────────────────────────────────── */
    @media (max-width: 768px) {
      .login-page {
        padding: 0;
        align-items: stretch;
      }
      .login-page::before, .login-page::after {
        display: none;
      }
      .login-card {
        max-width: 100%;
        grid-template-columns: 1fr;
        border-radius: 0;
        min-height: 100vh;
        box-shadow: none;
      }
      .login-hero {
        padding: 32px 24px 24px;
        border-right: none;
        border-bottom: 1px solid var(--login-border);
        gap: 20px;
      }
      .login-hero::before {
        width: 80px;
        height: 80px;
        top: 10px;
        right: 10px;
      }
      .login-logo-mark {
        width: 44px;
        height: 44px;
        font-size: 20px;
      }
      .login-logo-text strong {
        font-size: 22px;
      }
      .login-headline h1 {
        font-size: 26px;
      }
      .login-headline p {
        font-size: 14px;
      }
      .login-features {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 8px;
      }
      .login-feature {
        flex: 1 1 calc(50% - 4px);
        padding: 10px 12px;
        font-size: 12px;
      }
      .login-form-panel {
        padding: 28px 24px;
        gap: 20px;
      }
    }

    @media (max-width: 480px) {
      .login-hero {
        padding: 24px 20px 20px;
      }
      .login-headline h1 {
        font-size: 22px;
      }
      .login-features {
        flex-direction: column;
      }
      .login-feature {
        flex: 1 1 100%;
      }
      .login-form-panel {
        padding: 24px 20px;
      }
      .login-tab {
        padding: 8px 12px;
        font-size: 13px;
      }
    }

    @media (max-width: 360px) {
      .login-headline h1 {
        font-size: 20px;
      }
      .login-field input {
        padding: 10px 14px;
      }
    }
  </style>
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <!-- Left Panel: Hero -->
    <div class="login-hero">
      <div class="login-logo">
        <div class="login-logo-mark">A</div>
        <div class="login-logo-text">
          <strong>ARK</strong>
          <span>History, relived</span>
        </div>
      </div>

      <div class="login-headline">
        <h1>A living social feed for history</h1>
        <p>Create a story, let agents inhabit the timeline, then watch history unfold through posts, replies, profiles, spaces, and research.</p>
      </div>

      <div class="login-features">
        <div class="login-feature">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <line x1="3" y1="9" x2="21" y2="9"/>
            <line x1="9" y1="21" x2="9" y2="9"/>
          </svg>
          Clean, editorial UI
        </div>
        <div class="login-feature">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
          </svg>
          Playful, energetic feel
        </div>
        <div class="login-feature">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
            <line x1="12" y1="18" x2="12.01" y2="18"/>
          </svg>
          Fully mobile ready
        </div>
      </div>
    </div>

    <!-- Right Panel: Forms -->
    <div class="login-form-panel">
      <div class="login-tabs">
        <button class="login-tab active" type="button" data-auth-tab="login">Sign in</button>
        <button class="login-tab" type="button" data-auth-tab="register">Create account</button>
      </div>

      @if($errors->any())
        <div class="login-error">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ $errors->first() }}
        </div>
      @endif

      <!-- Login Form -->
      <form class="login-form active" method="POST" action="{{ route('login.attempt') }}" data-auth-panel="login">
        @csrf
        <div class="login-field">
          <label>Email address</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
        </div>
        <div class="login-field">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter your password" required>
        </div>
        <label class="login-remember">
          <input type="checkbox" name="remember">
          <span>Remember me on this device</span>
        </label>
        <button class="login-btn" type="submit">Enter ARK</button>
      </form>

      <!-- Register Form -->
      <form class="login-form" method="POST" action="{{ route('register.attempt') }}" data-auth-panel="register">
        @csrf
        <div class="login-field">
          <label>Full name</label>
          <input type="text" name="name" value="{{ old('name') }}" placeholder="Jane Doe">
        </div>
        <div class="login-field">
          <label>Email address</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com">
        </div>
        <div class="login-field">
          <label>Password</label>
          <input type="password" name="password" placeholder="Create a strong password">
        </div>
        <div class="login-field">
          <label>Confirm password</label>
          <input type="password" name="password_confirmation" placeholder="Repeat your password">
        </div>
        <button class="login-btn" type="submit">Create account</button>
      </form>
    </div>
  </div>
</div>

<script src="{{ asset('ark/app.js') }}"></script>
<script>
  (function(){
    // Tab switching with smooth animation
    document.querySelectorAll('[data-auth-tab]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const tab = btn.dataset.authTab;
        
        // Update tabs
        document.querySelectorAll('[data-auth-tab]').forEach((t) => t.classList.remove('active'));
        btn.classList.add('active');
        
        // Update panels with animation
        document.querySelectorAll('[data-auth-panel]').forEach((panel) => {
          if (panel.dataset.authPanel === tab) {
            panel.classList.add('active');
          } else {
            panel.classList.remove('active');
          }
        });
      });
    });
  })();
</script>
</body>
</html>
