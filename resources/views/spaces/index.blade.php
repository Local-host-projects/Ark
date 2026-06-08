@extends('layouts.app')
@php($title='ARK — Spaces')
@php($heading='Spaces')
@php($subtitle=$story->title)
@section('content')

<div class="spaces-page">
  <!-- Page Header -->
  <div class="spaces-header">
    <div class="spaces-header-content">
      <div class="spaces-count">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
        </svg>
        <span>{{ count($spaces) }} {{ Str::plural('space', count($spaces)) }}</span>
      </div>
    </div>
  </div>

  <!-- Spaces List -->
  <section class="spaces-list">
    @forelse($spaces as $space)
      <article class="space-card">
        <div class="space-card-inner">
          <!-- Left: Info -->
          <div class="space-info">
            <div class="space-title-row">
              <h2 class="space-title">{{ $space->title }}</h2>
              <div class="space-badges">
                <span class="space-status status-{{ $space->status }}">
                  <span class="status-dot"></span>
                  {{ $space->status }}
                </span>
                @if($space->isUnlockedFor($story))
                  <span class="space-badge unlocked">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Unlocked
                  </span>
                @endif
              </div>
            </div>
            <p class="space-description">{{ $space->description }}</p>
            <div class="space-meta">
              <span class="space-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
                Event {{ $space->unlocks_at_sequence }}
              </span>
              <span class="space-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                {{ $space->historical_date }}
              </span>
              @if($space->duration_seconds)
                <span class="space-meta-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                  </svg>
                  {{ $space->duration_seconds }}s
                </span>
              @endif
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="space-actions">
            <a href="{{ route('spaces.show', [$story, $space]) }}" class="space-btn space-btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="5 3 19 12 5 21 5 3"/>
              </svg>
              Open
            </a>
            @if(in_array($space->status, ['pending','failed']))
              <form method="POST" action="{{ route('spaces.generate', [$story, $space]) }}" class="space-generate-form">
                @csrf
                <button type="submit" class="space-btn space-btn-secondary">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                    <line x1="12" y1="19" x2="12" y2="23"/>
                    <line x1="8" y1="23" x2="16" y2="23"/>
                  </svg>
                  Generate audio
                </button>
              </form>
            @endif
          </div>
        </div>

        <!-- Audio Wave Decoration -->
        <div class="space-wave">
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
        </div>
      </article>
    @empty
      <div class="spaces-empty">
        <div class="spaces-empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
          </svg>
        </div>
        <h3>No spaces yet</h3>
        <p>No audio spaces have been created for this story yet.</p>
      </div>
    @endforelse
  </section>
</div>

<style>
  /* ── Spaces Page Styles ─────────────────────────────────────── */
  .spaces-page {
    padding: 0 20px 100px;
  }

  /* Header */
  .spaces-header {
    padding: 20px 0 16px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
  }

  .spaces-count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
  }
  .spaces-count svg {
    width: 18px;
    height: 18px;
    color: var(--accent);
  }

  /* List */
  .spaces-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  /* Card */
  .space-card {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
  }
  .space-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-strong);
  }

  .space-card-inner {
    padding: 24px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
  }

  /* Info */
  .space-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .space-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .space-title {
    font-size: 19px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    line-height: 1.2;
  }

  .space-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .space-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .space-status.status-ready {
    background: rgba(16,185,129,0.08);
    color: #059669;
    border: 1px solid rgba(16,185,129,0.15);
  }
  .space-status.status-ready .status-dot {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16,185,129,0.2);
  }

  .space-status.status-pending {
    background: rgba(245,158,11,0.08);
    color: #D97706;
    border: 1px solid rgba(245,158,11,0.15);
  }
  .space-status.status-pending .status-dot {
    background: #F59E0B;
    animation: pulse 2s infinite;
  }

  .space-status.status-failed {
    background: rgba(239,68,68,0.06);
    color: #DC2626;
    border: 1px solid rgba(239,68,68,0.12);
  }
  .space-status.status-failed .status-dot {
    background: #EF4444;
  }

  .space-status.status-locked {
    background: var(--bg-secondary);
    color: var(--text-muted);
    border: 1px solid var(--border);
  }
  .space-status.status-locked .status-dot {
    background: var(--text-muted);
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
  }

  .space-badge.unlocked {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    background: rgba(29,155,240,0.08);
    border: 1px solid rgba(29,155,240,0.15);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    color: var(--accent);
  }
  .space-badge.unlocked svg {
    width: 12px;
    height: 12px;
  }

  .space-description {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-secondary);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .space-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
  }

  .space-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
  }
  .space-meta-item svg {
    width: 14px;
    height: 14px;
    opacity: 0.5;
  }

  /* Actions */
  .space-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    flex-shrink: 0;
  }

  .space-generate-form {
    display: inline-flex;
  }

  .space-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.15s ease;
    white-space: nowrap;
  }
  .space-btn svg {
    width: 16px;
    height: 16px;
  }

  .space-btn-primary {
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: white;
    box-shadow: 0 4px 16px rgba(29,155,240,0.25);
  }
  .space-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(29,155,240,0.35);
  }

  .space-btn-secondary {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    color: var(--text-secondary);
  }
  .space-btn-secondary:hover {
    background: var(--surface-hover);
    border-color: var(--border-strong);
    color: var(--text-primary);
  }

  /* Audio Wave Decoration */
  .space-wave {
    display: flex;
    align-items: flex-end;
    gap: 3px;
    height: 32px;
    padding: 0 24px 16px;
    opacity: 0.06;
    position: relative;
    z-index: 0;
  }
  .space-wave span {
    flex: 1;
    background: var(--accent);
    border-radius: 999px;
    animation: wave 1.2s ease-in-out infinite;
  }
  .space-wave span:nth-child(1) { height: 20%; animation-delay: 0s; }
  .space-wave span:nth-child(2) { height: 45%; animation-delay: 0.1s; }
  .space-wave span:nth-child(3) { height: 70%; animation-delay: 0.2s; }
  .space-wave span:nth-child(4) { height: 35%; animation-delay: 0.3s; }
  .space-wave span:nth-child(5) { height: 85%; animation-delay: 0.4s; }
  .space-wave span:nth-child(6) { height: 50%; animation-delay: 0.5s; }
  .space-wave span:nth-child(7) { height: 25%; animation-delay: 0.6s; }
  .space-wave span:nth-child(8) { height: 60%; animation-delay: 0.7s; }
  .space-wave span:nth-child(9) { height: 40%; animation-delay: 0.8s; }
  .space-wave span:nth-child(10) { height: 75%; animation-delay: 0.9s; }
  .space-wave span:nth-child(11) { height: 30%; animation-delay: 1.0s; }
  .space-wave span:nth-child(12) { height: 55%; animation-delay: 1.1s; }
  .space-wave span:nth-child(13) { height: 15%; animation-delay: 0.05s; }
  .space-wave span:nth-child(14) { height: 65%; animation-delay: 0.15s; }
  .space-wave span:nth-child(15) { height: 45%; animation-delay: 0.25s; }
  .space-wave span:nth-child(16) { height: 80%; animation-delay: 0.35s; }
  .space-wave span:nth-child(17) { height: 35%; animation-delay: 0.45s; }
  .space-wave span:nth-child(18) { height: 50%; animation-delay: 0.55s; }
  .space-wave span:nth-child(19) { height: 70%; animation-delay: 0.65s; }
  .space-wave span:nth-child(20) { height: 25%; animation-delay: 0.75s; }

  @keyframes wave {
    0%, 100% { transform: scaleY(0.6); }
    50% { transform: scaleY(1); }
  }

  /* Empty State */
  .spaces-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 24px;
    text-align: center;
    gap: 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
  }
  .spaces-empty-icon {
    width: 64px;
    height: 64px;
    background: var(--bg-secondary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
  }
  .spaces-empty-icon svg {
    width: 28px;
    height: 28px;
  }
  .spaces-empty h3 {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
  }
  .spaces-empty p {
    font-size: 14px;
    color: var(--text-muted);
    max-width: 280px;
    line-height: 1.6;
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .spaces-page {
      padding: 0 14px 80px;
    }
    
    .spaces-header {
      padding: 14px 0 12px;
      margin-bottom: 14px;
    }
    
    .space-card-inner {
      padding: 18px;
      flex-direction: column;
      gap: 16px;
    }
    
    .space-title {
      font-size: 17px;
    }
    
    .space-actions {
      width: 100%;
    }
    
    .space-btn {
      flex: 1;
      justify-content: center;
      padding: 12px 18px;
    }
    
    .space-wave {
      padding: 0 18px 12px;
      height: 24px;
    }
    
    .spaces-empty {
      padding: 48px 20px;
    }
  }

  @media (max-width: 480px) {
    .spaces-page {
      padding: 0 12px 70px;
    }
    
    .space-card-inner {
      padding: 16px;
    }
    
    .space-title-row {
      gap: 8px;
    }
    
    .space-badges {
      width: 100%;
    }
    
    .space-status,
    .space-badge.unlocked {
      font-size: 10px;
      padding: 4px 10px;
    }
    
    .space-meta {
      gap: 12px;
    }
    
    .space-meta-item {
      font-size: 11px;
    }
    
    .space-wave {
      display: none;
    }
  }
</style>
@endsection