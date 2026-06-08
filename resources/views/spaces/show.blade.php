@extends('layouts.app')
@php($title='ARK — '.$space->title)
@php($heading=$space->title)
@php($subtitle=$story->title)
@section('content')

<div class="space-player-page">
  <!-- Space Info Card -->
  <section class="space-info-card">
    <div class="space-info-header">
      <div class="space-info-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
        </svg>
      </div>
      <div class="space-info-details">
        <h2 class="space-info-title">{{ $space->title }}</h2>
        <p class="space-info-desc">{{ $space->description }}</p>
      </div>
    </div>

    <div class="space-info-meta">
      <span class="space-info-tag status-{{ $space->status }}">
        <span class="status-dot"></span>
        {{ $space->status }}
      </span>
      <span class="space-info-tag">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        Event {{ $space->unlocks_at_sequence }}
      </span>
      <span class="space-info-tag">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        {{ $space->historical_date }}
      </span>
    </div>

    @if(!$space->isUnlockedFor($story))
      <div class="space-locked-banner">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <span>This space unlocks when the story reaches event {{ $space->unlocks_at_sequence }}.</span>
      </div>
    @endif
  </section>

  <!-- Audio Player -->
  @if($space->status === 'ready' && $space->isUnlockedFor($story) && count($playlist))
    <section class="space-audio-section">
      <!-- Visualizer -->
      <div class="space-visualizer" id="audioVisualizer">
        @for($i = 0; $i < 24; $i++)
          <span style="animation-delay: {{ $i * 0.08 }}s; height: {{ rand(15, 85) }}%;"></span>
        @endfor
      </div>

      <!-- Player Controls -->
      <div class="space-player-controls">
        <audio id="spaceAudio" controls style="width:100%;"></audio>
      </div>

      <!-- Queue -->
      <div class="space-queue-header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="8" y1="6" x2="21" y2="6"/>
          <line x1="8" y1="12" x2="21" y2="12"/>
          <line x1="8" y1="18" x2="21" y2="18"/>
          <line x1="3" y1="6" x2="3.01" y2="6"/>
          <line x1="3" y1="12" x2="3.01" y2="12"/>
          <line x1="3" y1="18" x2="3.01" y2="18"/>
        </svg>
        <span>Playback Queue</span>
        <div class="space-queue-count">{{ count($playlist) }} clips</div>
      </div>

      <div class="space-queue" id="audioQueue">
        @foreach($playlist as $index => $line)
          <button type="button" class="space-queue-item {{ $index === 0 ? 'active' : '' }}" data-audio-line data-src="{{ $line['url'] }}" data-index="{{ $index }}">
            <div class="queue-item-number">{{ $index + 1 }}</div>
            <div class="queue-item-wave">
              @for($w = 0; $w < 12; $w++)
                <span style="height: {{ rand(20, 80) }}%;"></span>
              @endfor
            </div>
            <div class="queue-item-info">
              <strong>{{ $line['agent_name'] }}</strong>
              <span>{{ $line['duration_seconds'] ?? '?' }}s</span>
            </div>
            <div class="queue-item-play">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <polygon points="5 3 19 12 5 21 5 3"/>
              </svg>
            </div>
          </button>
        @endforeach
      </div>
    </section>

  @else
    <!-- Not Ready State -->
    <section class="space-not-ready">
      <div class="space-not-ready-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
          <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
          <line x1="12" y1="19" x2="12" y2="23"/>
          <line x1="8" y1="23" x2="16" y2="23"/>
        </svg>
      </div>
      <h3>Audio not ready</h3>
      <p>This space's audio hasn't been generated yet.</p>
      @if(in_array($space->status, ['pending','failed']))
        <form method="POST" action="{{ route('spaces.generate', [$story, $space]) }}">
          @csrf
          <button type="submit" class="space-generate-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
              <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
              <line x1="12" y1="19" x2="12" y2="23"/>
              <line x1="8" y1="23" x2="16" y2="23"/>
            </svg>
            Generate space audio
          </button>
        </form>
      @endif
    </section>
  @endif
</div>

<style>
  /* ── Space Player Page Styles ───────────────────────────────── */
  .space-player-page {
    padding: 0 20px 100px;
  }

  /* Info Card */
  .space-info-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
  }
  .space-info-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(29,155,240,0.06) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .space-info-header {
    display: flex;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
  }

  .space-info-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 20px rgba(29,155,240,0.25);
  }
  .space-info-icon svg {
    width: 28px;
    height: 28px;
  }

  .space-info-details {
    flex: 1;
    min-width: 0;
  }

  .space-info-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    margin-bottom: 6px;
    line-height: 1.2;
  }

  .space-info-desc {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-secondary);
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .space-info-meta {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
  }

  .space-info-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
  }
  .space-info-tag svg {
    width: 14px;
    height: 14px;
    opacity: 0.6;
  }

  .space-info-tag.status-ready {
    background: rgba(16,185,129,0.08);
    border-color: rgba(16,185,129,0.15);
    color: #059669;
  }
  .space-info-tag.status-ready .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16,185,129,0.2);
  }

  .space-info-tag.status-pending {
    background: rgba(245,158,11,0.08);
    border-color: rgba(245,158,11,0.15);
    color: #D97706;
  }
  .space-info-tag.status-pending .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #F59E0B;
    animation: pulse 2s infinite;
  }

  .space-info-tag.status-failed {
    background: rgba(239,68,68,0.06);
    border-color: rgba(239,68,68,0.12);
    color: #DC2626;
  }

  .space-info-tag.status-locked {
    background: var(--bg-secondary);
    color: var(--text-muted);
  }

  /* Locked Banner */
  .space-locked-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 20px;
    padding: 14px 18px;
    background: var(--yellow-soft);
    border: 1px solid rgba(245,158,11,0.15);
    border-radius: 14px;
    font-size: 13px;
    font-weight: 700;
    color: #6a6500;
    position: relative;
    z-index: 1;
  }
  .space-locked-banner svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    opacity: 0.7;
  }

  /* Audio Section */
  .space-audio-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
  }

  /* Visualizer */
  .space-visualizer {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 3px;
    height: 80px;
    padding: 20px 24px 0;
    background: linear-gradient(180deg, var(--accent-bg) 0%, transparent 100%);
    opacity: 0.4;
  }
  .space-visualizer span {
    flex: 1;
    max-width: 8px;
    background: var(--accent);
    border-radius: 999px;
    animation: visualizer 1s ease-in-out infinite;
    transition: height 0.3s ease;
  }
  .space-visualizer.paused span {
    animation-play-state: paused;
    height: 10% !important;
  }

  @keyframes visualizer {
    0%, 100% { transform: scaleY(0.5); }
    50% { transform: scaleY(1); }
  }

  /* Player Controls */
  .space-player-controls {
    padding: 16px 24px;
    border-bottom: 1px solid var(--border);
  }
  .space-player-controls audio {
    width: 100%;
    height: 40px;
    border-radius: 12px;
  }

  /* Queue Header */
  .space-queue-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 24px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border);
  }
  .space-queue-header svg {
    width: 18px;
    height: 18px;
    color: var(--accent);
  }
  .space-queue-count {
    margin-left: auto;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    background: var(--bg-secondary);
    padding: 4px 12px;
    border-radius: 999px;
  }

  /* Queue Items */
  .space-queue {
    display: flex;
    flex-direction: column;
  }

  .space-queue-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 24px;
    border: none;
    border-bottom: 1px solid var(--border);
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s ease;
    width: 100%;
  }
  .space-queue-item:last-child {
    border-bottom: none;
  }
  .space-queue-item:hover {
    background: var(--surface-hover);
  }
  .space-queue-item.active {
    background: var(--accent-bg);
  }

  .queue-item-number {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    border-radius: 8px;
    font-size: 12px;
    font-weight: 800;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: all 0.15s;
  }
  .space-queue-item.active .queue-item-number {
    background: var(--accent);
    color: white;
  }

  .queue-item-wave {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    width: 48px;
    height: 28px;
    flex-shrink: 0;
  }
  .queue-item-wave span {
    flex: 1;
    background: var(--border-strong);
    border-radius: 999px;
    transition: all 0.3s ease;
  }
  .space-queue-item.active .queue-item-wave span {
    background: var(--accent);
    animation: queueWave 0.8s ease-in-out infinite;
  }
  @keyframes queueWave {
    0%, 100% { transform: scaleY(0.6); }
    50% { transform: scaleY(1); }
  }

  .queue-item-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .queue-item-info strong {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .queue-item-info span {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
  }

  .queue-item-play {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    border-radius: 10px;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: all 0.15s;
  }
  .queue-item-play svg {
    width: 14px;
    height: 14px;
  }
  .space-queue-item:hover .queue-item-play {
    background: var(--accent);
    color: white;
    transform: scale(1.05);
  }
  .space-queue-item.active .queue-item-play {
    background: var(--accent);
    color: white;
  }

  /* Not Ready State */
  .space-not-ready {
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
    box-shadow: var(--shadow-sm);
  }
  .space-not-ready-icon {
    width: 72px;
    height: 72px;
    background: var(--bg-secondary);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
  }
  .space-not-ready-icon svg {
    width: 32px;
    height: 32px;
  }
  .space-not-ready h3 {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
  }
  .space-not-ready p {
    font-size: 14px;
    color: var(--text-muted);
    max-width: 300px;
    line-height: 1.6;
  }

  .space-generate-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
  }
  .space-generate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(29,155,240,0.35);
  }
  .space-generate-btn svg {
    width: 18px;
    height: 18px;
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .space-player-page {
      padding: 0 14px 80px;
    }
    
    .space-info-card {
      padding: 20px;
      border-radius: 16px;
    }
    
    .space-info-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
    }
    .space-info-icon svg {
      width: 24px;
      height: 24px;
    }
    
    .space-info-title {
      font-size: 19px;
    }
    
    .space-visualizer {
      height: 60px;
      padding: 16px 20px 0;
    }
    
    .space-player-controls {
      padding: 14px 20px;
    }
    
    .space-queue-header {
      padding: 14px 20px;
    }
    
    .space-queue-item {
      padding: 12px 20px;
      gap: 12px;
    }
    
    .queue-item-wave {
      display: none;
    }
    
    .space-not-ready {
      padding: 48px 20px;
    }
    
    .space-not-ready-icon {
      width: 60px;
      height: 60px;
      border-radius: 20px;
    }
  }

  @media (max-width: 480px) {
    .space-player-page {
      padding: 0 12px 70px;
    }
    
    .space-info-card {
      padding: 16px;
    }
    
    .space-info-header {
      gap: 14px;
    }
    
    .space-info-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
    }
    
    .space-info-title {
      font-size: 17px;
    }
    
    .space-info-tag {
      font-size: 11px;
      padding: 5px 12px;
    }
    
    .space-locked-banner {
      font-size: 12px;
      padding: 12px 14px;
    }
    
    .queue-item-number {
      width: 24px;
      height: 24px;
      font-size: 11px;
    }
    
    .queue-item-info strong {
      font-size: 13px;
    }
    
    .queue-item-play {
      width: 32px;
      height: 32px;
      border-radius: 8px;
    }
  }
</style>

<script>
  (function(){
    const audio = document.getElementById('spaceAudio');
    const visualizer = document.getElementById('audioVisualizer');
    const items = [...document.querySelectorAll('[data-audio-line]')];
    
    if (!audio || !items.length) return;
    
    let current = 0;
    
    const playIndex = (index) => {
      current = index;
      items.forEach((item, i) => {
        item.classList.toggle('active', i === index);
      });
      audio.src = items[index].dataset.src;
      audio.play().catch(() => {});
      if (visualizer) visualizer.classList.remove('paused');
    };
    
    items.forEach((item, idx) => {
      item.addEventListener('click', () => playIndex(idx));
    });
    
    audio.addEventListener('play', () => {
      if (visualizer) visualizer.classList.remove('paused');
    });
    
    audio.addEventListener('pause', () => {
      if (visualizer) visualizer.classList.add('paused');
    });
    
    audio.addEventListener('ended', () => {
      if (current + 1 < items.length) {
        playIndex(current + 1);
      } else {
        items.forEach((item) => item.classList.remove('active'));
        if (visualizer) visualizer.classList.add('paused');
      }
    });
    
    // Auto-play first on load
    playIndex(0);
  })();
</script>
@endsection