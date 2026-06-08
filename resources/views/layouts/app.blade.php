<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'ARK' }}</title>
  <link rel="stylesheet" href="{{ asset('ark/app.css') }}">
  <style>
    /* ── App Shell Overrides & Enhancements ───────────────────── */
    :root {
      --sidebar-bg: rgba(255, 255, 255, 0.85);
      --sidebar-border: rgba(0,0,0,0.06);
      --nav-icon-size: 20px;
      --bottom-nav-height: 64px;
      --header-blur: blur(24px) saturate(1.2);
    }
    body.dark {
      --sidebar-bg: rgba(15, 23, 42, 0.88);
      --sidebar-border: rgba(255,255,255,0.05);
    }

    /* ── Critical Mobile Interaction Fixes ────────────────────── */
    a, button, input, textarea, select, [role="button"] {
      touch-action: manipulation;
      -webkit-tap-highlight-color: transparent;
    }
    input, textarea {
      -webkit-appearance: none;
      appearance: none;
      font-size: 16px;
    }
    button, [type="submit"], [type="button"] {
      cursor: pointer;
      user-select: none;
      -webkit-user-select: none;
    }
    .feed-column, .sidebar, .space-queue {
      -webkit-overflow-scrolling: touch;
    }

    /* ── Sidebar ── */
    .sidebar {
      background: var(--sidebar-bg);
      backdrop-filter: var(--header-blur);
      -webkit-backdrop-filter: var(--header-blur);
      border-right: 1px solid var(--sidebar-border);
      padding: 20px 14px;
      transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), background 0.2s;
      z-index: 200;
    }

    .sidebar-logo {
      padding: 10px 12px;
      margin-bottom: 6px;
      border-radius: 16px;
      transition: background 0.15s;
    }
    .sidebar-logo:hover {
      background: var(--surface-hover);
    }
    .sidebar-logo .logo-mark {
      width: 38px;
      height: 38px;
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(37,99,235,0.25);
      font-size: 20px;
    }

    .sidebar-nav {
      margin-top: 4px;
      gap: 3px;
    }

    .nav-item {
      padding: 11px 14px;
      border-radius: 14px;
      font-size: 14px;
      font-weight: 600;
      color: var(--text-muted);
      position: relative;
      overflow: hidden;
    }
    .nav-item::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%) scaleY(0);
      width: 3px;
      height: 20px;
      background: var(--accent);
      border-radius: 0 3px 3px 0;
      transition: transform 0.2s ease;
    }
    .nav-item:hover {
      background: var(--surface-hover);
      color: var(--text-primary);
    }
    .nav-item.active {
      background: var(--accent-bg);
      color: var(--accent);
    }
    .nav-item.active::before {
      transform: translateY(-50%) scaleY(1);
    }
    .nav-item svg {
      width: var(--nav-icon-size);
      height: var(--nav-icon-size);
      opacity: 0.7;
      transition: opacity 0.15s;
    }
    .nav-item:hover svg,
    .nav-item.active svg {
      opacity: 1;
    }

    /* Story pills in sidebar */
    .story-pill-sidebar {
      display: block;
      padding: 10px 14px;
      border-radius: 14px;
      border: 1px solid var(--border);
      background: var(--surface);
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }
    .story-pill-sidebar::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 3px;
      background: var(--accent);
      opacity: 0;
      transition: opacity 0.2s;
    }
    .story-pill-sidebar:hover {
      border-color: var(--accent);
      transform: translateX(2px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .story-pill-sidebar.active {
      border-color: rgba(29,155,240,0.3);
      background: var(--accent-bg);
    }
    .story-pill-sidebar.active::before {
      opacity: 1;
    }
    .story-pill-sidebar .story-title {
      font-weight: 700;
      font-size: 13px;
      color: var(--text-primary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .story-pill-sidebar .story-meta {
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 3px;
    }

    /* User chip */
    .user-chip {
      padding: 10px 12px;
      border-radius: 14px;
      border: 1px solid var(--border);
      background: var(--surface);
      transition: all 0.15s;
    }
    .user-chip:hover {
      border-color: var(--border-strong);
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .user-chip .avatar {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, var(--accent), var(--accent-dark));
      font-size: 14px;
      font-weight: 700;
    }

    /* Theme toggle */
    .theme-toggle {
      padding: 10px 14px;
      border-radius: 14px;
      font-size: 13px;
      font-weight: 600;
      border: 1px solid var(--border);
      background: var(--surface);
    }
    .theme-toggle:hover {
      border-color: var(--border-strong);
    }
    .theme-toggle svg {
      width: 18px;
      height: 18px;
    }

    /* ── Main Content ── */
    .main {
      margin-left: var(--sidebar-width);
      min-height: 100vh;
      position: relative;
      z-index: 1;
    }
    .feed-column {
      width: 100%;
      max-width: 640px;
      border-left: 1px solid var(--border);
      border-right: 1px solid var(--border);
      min-height: 100vh;
      position: relative;
      z-index: 1;
    }

    /* ── Column Header ── */
    .col-header {
      padding: 12px 20px;
      background: var(--bg-glass);
      backdrop-filter: var(--header-blur);
      -webkit-backdrop-filter: var(--header-blur);
      border-bottom: 1px solid var(--border);
      gap: 14px;
      pointer-events: auto;
    }
    .col-header h1 {
      font-size: 18px;
      font-weight: 800;
      letter-spacing: -0.4px;
    }
    .col-header .subtitle {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 500;
      margin-top: 2px;
    }

    /* Hamburger */
    .hamburger {
      display: none;
      width: 38px;
      height: 38px;
      border-radius: 12px;
      background: var(--surface);
      border: 1px solid var(--border);
      color: var(--text-primary);
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all 0.15s;
      pointer-events: auto;
      z-index: 60;
    }
    .hamburger:hover {
      background: var(--surface-hover);
      border-color: var(--border-strong);
    }
    .hamburger svg {
      width: 18px;
      height: 18px;
    }

    /* ── Status / Error Banners ── */
    .status-banner {
      padding: 12px 20px;
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .status-banner.success {
      background: var(--accent-bg);
      color: var(--accent-dark);
      border-bottom: 1px solid rgba(29,155,240,0.12);
    }
    .status-banner.error {
      background: rgba(244,33,46,0.06);
      color: var(--red);
      border-bottom: 1px solid rgba(244,33,46,0.1);
    }

    /* ── Bottom Navigation (Mobile) ── */
    .bottom-nav {
      display: none;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: var(--bottom-nav-height);
      background: var(--sidebar-bg);
      backdrop-filter: var(--header-blur);
      -webkit-backdrop-filter: var(--header-blur);
      border-top: 1px solid var(--sidebar-border);
      z-index: 100;
      padding: 0 4px;
      padding-bottom: env(safe-area-inset-bottom, 0px);
      pointer-events: auto;
      touch-action: none;
    }
    .bottom-nav-inner {
      display: flex;
      align-items: center;
      justify-content: space-around;
      height: 100%;
      max-width: 500px;
      margin: 0 auto;
    }
    .bottom-nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 3px;
      padding: 6px 10px;
      border-radius: 12px;
      color: var(--text-muted);
      font-size: 10px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.15s;
      flex: 1;
      max-width: 72px;
      position: relative;
      pointer-events: auto;
    }
    .bottom-nav-item::after {
      content: '';
      position: absolute;
      bottom: 4px;
      width: 4px;
      height: 4px;
      border-radius: 50%;
      background: var(--accent);
      opacity: 0;
      transition: opacity 0.2s;
    }
    .bottom-nav-item.active {
      color: var(--accent);
    }
    .bottom-nav-item.active::after {
      opacity: 1;
    }
    .bottom-nav-item:active {
      background: var(--surface-hover);
    }
    .bottom-nav-item svg {
      width: 22px;
      height: 22px;
      transition: transform 0.15s;
    }
    .bottom-nav-item:active svg {
      transform: scale(0.92);
    }

    /* ── Sidebar Backdrop ── */
    .sidebar-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.35);
      backdrop-filter: blur(4px);
      z-index: 199;
      opacity: 0;
      transition: opacity 0.3s ease;
      pointer-events: none;
    }
    .sidebar-backdrop.show {
      display: block;
      opacity: 1;
      pointer-events: auto;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-105%);
        width: 280px;
        box-shadow: var(--shadow-lg);
      }
      .sidebar.open {
        transform: translateX(0);
      }
      .sidebar-backdrop {
        display: none;
      }
      .sidebar-backdrop.show {
        display: block;
      }
      .main {
        margin-left: 0;
      }
      .feed-column {
        border: none;
        max-width: 100%;
        padding-bottom: calc(var(--bottom-nav-height) + env(safe-area-inset-bottom, 0px) + 12px);
      }
      .hamburger {
        display: flex;
      }
      .bottom-nav {
        display: block;
      }
      .col-header {
        padding: 10px 16px;
        gap: 12px;
      }
      .col-header h1 {
        font-size: 16px;
      }
    }

    @media (max-width: 480px) {
      .col-header {
        padding: 8px 14px;
      }
    }
  </style>
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <a href="{{ $currentStory ? route('feed.show', $currentStory) : route('stories.index') }}" class="sidebar-logo">
      <div class="logo-mark">A</div>
      <div>
        <div class="logo-text">ARK</div>
        <div style="font-size:12px;color:var(--text-muted);font-weight:500;">History, relived</div>
      </div>
    </a>

    <nav class="sidebar-nav">
      <a class="nav-item {{ request()->routeIs('stories.*') ? 'active' : '' }}" href="{{ route('stories.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
          <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        </svg>
        Stories
      </a>
      @if($currentStory)
        <a class="nav-item {{ request()->routeIs('feed.*') ? 'active' : '' }}" href="{{ route('feed.show', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          Feed
        </a>
        <a class="nav-item {{ request()->routeIs('timeline.*') ? 'active' : '' }}" href="{{ route('timeline.show', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          Timeline
        </a>
        <a class="nav-item {{ request()->routeIs('agents.*') ? 'active' : '' }}" href="{{ route('agents.index', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Agents
        </a>
        <a class="nav-item {{ request()->routeIs('spaces.*') ? 'active' : '' }}" href="{{ route('spaces.index', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
          </svg>
          Spaces
        </a>
        <a class="nav-item {{ request()->routeIs('research.*') ? 'active' : '' }}" href="{{ route('research.show', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            <line x1="11" y1="8" x2="11" y2="14"/>
            <line x1="8" y1="11" x2="14" y2="11"/>
          </svg>
          Research
        </a>
      @endif
    </nav>

    <div class="sidebar-bottom">
      @if($storyList->count())
      <div style="padding:0 10px;display:grid;gap:8px;margin-bottom:8px;">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);font-weight:700;">Your stories</div>
        @foreach($storyList->take(5) as $storyItem)
          <a href="{{ route('feed.show', $storyItem) }}" class="story-pill-sidebar {{ $currentStory && $currentStory->id === $storyItem->id ? 'active' : '' }}">
            <div class="story-title">{{ $storyItem->title }}</div>
            <div class="story-meta">{{ $storyItem->status }} &middot; {{ $storyItem->current_sequence }}/{{ count($storyItem->timeline ?? []) }}</div>
          </a>
        @endforeach
      </div>
      @endif

      <button class="theme-toggle" type="button" data-theme-toggle>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="5"/>
          <line x1="12" y1="1" x2="12" y2="3"/>
          <line x1="12" y1="21" x2="12" y2="23"/>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
          <line x1="1" y1="12" x2="3" y2="12"/>
          <line x1="21" y1="12" x2="23" y2="12"/>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
        Toggle theme
      </button>

      <div class="user-chip">
        <div class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
        <div class="user-info">
          <div class="user-name">{{ auth()->user()->name }}</div>
          <div class="user-email">{{ auth()->user()->email }}</div>
        </div>
      </div>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn-secondary" style="width:100%;font-size:13px;padding:10px 14px;" type="submit">
          <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          Log out
        </button>
      </form>
    </div>
  </aside>

  <div class="sidebar-backdrop" id="sidebarBackdrop" style="display:none;"></div>

  <main class="main">
    <div class="feed-column">
      <header class="col-header">
        <button class="hamburger" type="button" data-sidebar-open aria-label="Open navigation">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
        <div style="flex:1;min-width:0;">
          <h1>{{ $heading ?? 'ARK' }}</h1>
          @isset($subtitle)<div class="subtitle">{{ $subtitle }}</div>@endisset
        </div>
        @yield('header-actions')
      </header>

      @if(session('status'))
        <div class="status-banner success">
          <svg style="width:16px;height:16px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          {{ session('status') }}
        </div>
      @endif
      @if($errors->any())
        <div class="status-banner error">
          <svg style="width:16px;height:16px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ $errors->first() }}
        </div>
      @endif

      @yield('content')
    </div>
  </main>

  <!-- Bottom Navigation (Mobile) -->
  <nav class="bottom-nav">
    <div class="bottom-nav-inner">
      <a class="bottom-nav-item {{ request()->routeIs('stories.*') ? 'active' : '' }}" href="{{ route('stories.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
          <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        </svg>
        <span>Stories</span>
      </a>
      @if($currentStory)
        <a class="bottom-nav-item {{ request()->routeIs('feed.*') ? 'active' : '' }}" href="{{ route('feed.show', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          <span>Feed</span>
        </a>
        <a class="bottom-nav-item {{ request()->routeIs('timeline.*') ? 'active' : '' }}" href="{{ route('timeline.show', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          <span>Time</span>
        </a>
        <a class="bottom-nav-item {{ request()->routeIs('agents.*') ? 'active' : '' }}" href="{{ route('agents.index', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <span>Agents</span>
        </a>
        <a class="bottom-nav-item {{ request()->routeIs('spaces.*') ? 'active' : '' }}" href="{{ route('spaces.index', $currentStory) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
          </svg>
          <span>Spaces</span>
        </a>
      @else
        <a class="bottom-nav-item" href="{{ route('stories.index') }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>New</span>
        </a>
      @endif
    </div>
  </nav>
</div>

<script src="{{ asset('ark/app.js') }}"></script>
@stack('scripts')
</body>
</html>