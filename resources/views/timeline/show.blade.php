@extends('layouts.app')
@php($title='ARK — Timeline')
@php($heading='Timeline')
@php($subtitle=$story->title)
@section('content')

<div class="timeline-page">
  <!-- Arc Header -->
  <section class="timeline-arc">
    <div class="timeline-arc-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
    </div>
    <div class="timeline-arc-content">
      <h2>Dramatic arc</h2>
      <p>This story is shaped as a dramatic timeline so the feed keeps its liveness and narrative tension.</p>
    </div>
  </section>

  @if(empty($timeline) || count($timeline) === 0)
    <!-- Loading State -->
    <div class="timeline-loading">
      <div class="timeline-loading-visual">
        <div class="timeline-loading-node">
          <div class="timeline-loading-ring"></div>
          <div class="timeline-loading-dot"></div>
        </div>
        <div class="timeline-loading-line">
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="timeline-loading-node secondary">
          <div class="timeline-loading-ring"></div>
          <div class="timeline-loading-dot"></div>
        </div>
        <div class="timeline-loading-line">
          <span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="timeline-loading-node tertiary">
          <div class="timeline-loading-ring"></div>
          <div class="timeline-loading-dot"></div>
        </div>
      </div>
      <div class="timeline-loading-text">
        <h3>Generating timeline...</h3>
        <p>ARK is crafting the dramatic arc for this story. Events will appear here as soon as they're ready.</p>
        <div class="timeline-loading-progress">
          <div class="timeline-loading-track">
            <div class="timeline-loading-fill"></div>
          </div>
          <span>Building narrative structure</span>
        </div>
      </div>
    </div>
  @else
    <!-- Timeline Track -->
    <section class="timeline-track">
      <div class="timeline-line-bg"></div>

      @forelse($timeline as $index => $event)
        @php
          $isUnlocked = ($event['sequence'] ?? 0) <= $story->current_sequence;
          $beat = $event['beat'] ?? 'exposition';
          $beatColors = [
            'exposition' => ['bg' => '#1D9BF0', 'soft' => 'rgba(29,155,240,0.08)', 'text' => '#1D9BF0'],
            'inciting' => ['bg' => '#F59E0B', 'soft' => 'rgba(245,158,11,0.08)', 'text' => '#D97706'],
            'rising' => ['bg' => '#3B82F6', 'soft' => 'rgba(59,130,246,0.08)', 'text' => '#2563EB'],
            'crisis' => ['bg' => '#EF4444', 'soft' => 'rgba(239,68,68,0.08)', 'text' => '#DC2626'],
            'climax' => ['bg' => '#10B981', 'soft' => 'rgba(16,185,129,0.08)', 'text' => '#059669'],
          ];
          $colors = $beatColors[$beat] ?? $beatColors['exposition'];
        @endphp

        <article class="timeline-event {{ $isUnlocked ? 'unlocked' : 'locked' }}" data-beat="{{ $beat }}">
          <!-- Node -->
          <div class="timeline-node">
            <div class="timeline-node-ring" style="border-color: {{ $colors['bg'] }}33;"></div>
            <div class="timeline-node-dot" style="background: {{ $isUnlocked ? $colors['bg'] : 'var(--border-strong)' }}; box-shadow: {{ $isUnlocked ? '0 0 0 4px '.$colors['soft'] : 'none' }};">
              @if($isUnlocked)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              @endif
            </div>
          </div>

          <!-- Card -->
          <div class="timeline-card">
            <div class="timeline-card-accent" style="background: {{ $colors['bg'] }};"></div>
            
            <div class="timeline-card-header">
              <div class="timeline-card-title-wrap">
                <h3>{{ $event['title'] }}</h3>
                <span class="timeline-beat" style="background: {{ $colors['soft'] }}; color: {{ $colors['text'] }}; border: 1px solid {{ $colors['bg'] }}22;">
                  <span class="beat-dot" style="background: {{ $colors['bg'] }};"></span>
                  {{ str_replace('_',' ', $beat) }}
                </span>
                @if($isUnlocked)
                  <span class="timeline-unlocked-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Unlocked
                  </span>
                @endif
              </div>
              <div class="timeline-card-meta">
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                  </svg>
                  {{ $event['date'] ?? 'Unknown date' }}
                </span>
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                  {{ $event['location']['name'] ?? 'Unknown place' }}
                </span>
              </div>
            </div>

            <p class="timeline-description">{{ $event['description'] }}</p>

            <div class="timeline-footer">
              <span class="timeline-emotion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                Emotion: {{ $event['emotional_register'] ?? 'steady' }}
              </span>
              @if(!empty($event['cliffhanger']))
                <span class="timeline-cliffhanger">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                  </svg>
                  {{ $event['cliffhanger'] }}
                </span>
              @endif
              @if(!empty($event['scheduled_at']))
                <span class="timeline-countdown" data-countdown="{{ $event['scheduled_at'] }}">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                  </svg>
                  <span>Loading...</span>
                </span>
              @endif
            </div>
          </div>
        </article>
      @endforelse
    </section>
  @endif
</div>

<style>
  /* ── Timeline Page Styles ───────────────────────────────────── */
  .timeline-page {
    padding: 0 20px 100px;
  }

  /* Arc Header */
  .timeline-arc {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: flex-start;
    gap: 18px;
    position: relative;
    overflow: hidden;
  }
  .timeline-arc::before {
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

  .timeline-arc-icon {
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
    position: relative;
    z-index: 1;
  }
  .timeline-arc-icon svg {
    width: 28px;
    height: 28px;
  }

  .timeline-arc-content {
    flex: 1;
    min-width: 0;
    position: relative;
    z-index: 1;
  }
  .timeline-arc-content h2 {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    margin-bottom: 6px;
  }
  .timeline-arc-content p {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.6;
    max-width: 50ch;
  }

  /* ── Loading State ─────────────────────────────────────────── */
  .timeline-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 24px;
    text-align: center;
    gap: 32px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
  }
  .timeline-loading::before {
    content: '';
    position: absolute;
    top: -30%;
    left: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(29,155,240,0.04) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .timeline-loading-visual {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
    position: relative;
    z-index: 1;
  }

  .timeline-loading-node {
    position: relative;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .timeline-loading-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid var(--border);
    animation: loadingRing 2s ease-in-out infinite;
  }
  .timeline-loading-node.secondary .timeline-loading-ring {
    animation-delay: 0.4s;
    border-color: var(--accent);
    opacity: 0.3;
  }
  .timeline-loading-node.tertiary .timeline-loading-ring {
    animation-delay: 0.8s;
    border-color: var(--green);
    opacity: 0.2;
  }

  @keyframes loadingRing {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 1; }
  }

  .timeline-loading-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    animation: loadingDot 1.5s ease-in-out infinite;
    box-shadow: 0 0 20px rgba(29,155,240,0.3);
  }
  .timeline-loading-node.secondary .timeline-loading-dot {
    width: 12px;
    height: 12px;
    background: linear-gradient(135deg, var(--accent), var(--accent-light));
    animation-delay: 0.3s;
    opacity: 0.6;
  }
  .timeline-loading-node.tertiary .timeline-loading-dot {
    width: 10px;
    height: 10px;
    background: var(--green);
    animation-delay: 0.6s;
    opacity: 0.4;
  }

  @keyframes loadingDot {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.3); opacity: 1; }
  }

  .timeline-loading-line {
    display: flex;
    flex-direction: column;
    gap: 4px;
    height: 48px;
    justify-content: center;
    align-items: center;
  }
  .timeline-loading-line span {
    width: 2px;
    background: var(--border);
    border-radius: 999px;
    animation: loadingLine 1.2s ease-in-out infinite;
  }
  .timeline-loading-line span:nth-child(1) { height: 8px; animation-delay: 0s; }
  .timeline-loading-line span:nth-child(2) { height: 16px; animation-delay: 0.1s; }
  .timeline-loading-line span:nth-child(3) { height: 24px; animation-delay: 0.2s; }
  .timeline-loading-line span:nth-child(4) { height: 12px; animation-delay: 0.3s; }
  .timeline-loading-line span:nth-child(5) { height: 20px; animation-delay: 0.4s; }
  .timeline-loading-line span:nth-child(6) { height: 14px; animation-delay: 0.5s; }
  .timeline-loading-line span:nth-child(7) { height: 22px; animation-delay: 0.6s; }
  .timeline-loading-line span:nth-child(8) { height: 10px; animation-delay: 0.7s; }
  .timeline-loading-line span:nth-child(9) { height: 18px; animation-delay: 0.8s; }
  .timeline-loading-line span:nth-child(10) { height: 6px; animation-delay: 0.9s; }

  @keyframes loadingLine {
    0%, 100% { transform: scaleY(0.5); opacity: 0.3; }
    50% { transform: scaleY(1); opacity: 1; }
  }

  .timeline-loading-text {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    position: relative;
    z-index: 1;
  }
  .timeline-loading-text h3 {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
  }
  .timeline-loading-text p {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.6;
    max-width: 40ch;
  }

  .timeline-loading-progress {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
    width: 100%;
    max-width: 280px;
  }

  .timeline-loading-track {
    width: 100%;
    height: 6px;
    background: var(--bg-secondary);
    border-radius: 999px;
    overflow: hidden;
    position: relative;
  }

  .timeline-loading-fill {
    height: 100%;
    width: 30%;
    background: linear-gradient(90deg, var(--accent), var(--accent-light));
    border-radius: 999px;
    animation: loadingProgress 2s ease-in-out infinite;
    position: relative;
  }
  .timeline-loading-fill::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 40px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    border-radius: 0 999px 999px 0;
    animation: loadingShimmer 1.5s ease-in-out infinite;
  }

  @keyframes loadingProgress {
    0% { width: 15%; }
    50% { width: 65%; }
    100% { width: 15%; }
  }

  @keyframes loadingShimmer {
    0% { transform: translateX(-40px); opacity: 0; }
    50% { opacity: 1; }
    100% { transform: translateX(40px); opacity: 0; }
  }

  .timeline-loading-progress span {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.3px;
  }

  /* ── Track ── */
  .timeline-track {
    position: relative;
    padding-left: 24px;
  }

  .timeline-line-bg {
    position: absolute;
    left: 39px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
    border-radius: 999px;
  }

  /* Event */
  .timeline-event {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 24px;
    padding-bottom: 32px;
  }
  .timeline-event:last-child {
    padding-bottom: 0;
  }

  /* Node */
  .timeline-node {
    position: relative;
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    margin-top: 20px;
    z-index: 1;
  }

  .timeline-node-ring {
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    opacity: 0;
  }
  .timeline-event:hover .timeline-node-ring {
    opacity: 1;
    transform: scale(1.2);
  }

  .timeline-node-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
    position: relative;
  }
  .timeline-node-dot svg {
    width: 14px;
    height: 14px;
  }

  .timeline-event.locked .timeline-node-dot {
    background: var(--bg-secondary) !important;
    border: 2px solid var(--border-strong);
    color: var(--text-muted);
    box-shadow: none !important;
  }
  .timeline-event.locked .timeline-node-dot svg {
    width: 12px;
    height: 12px;
  }

  /* Card */
  .timeline-card {
    flex: 1;
    min-width: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow-sm);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  .timeline-event:hover .timeline-card {
    transform: translateX(4px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-strong);
  }

  .timeline-card-accent {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    transition: width 0.2s ease;
  }
  .timeline-event:hover .timeline-card-accent {
    width: 6px;
  }

  /* Header */
  .timeline-card-header {
    margin-bottom: 14px;
  }

  .timeline-card-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }

  .timeline-card h3 {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    line-height: 1.2;
  }

  .timeline-beat {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .timeline-beat .beat-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .timeline-unlocked-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.15);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    color: #059669;
  }
  .timeline-unlocked-badge svg {
    width: 12px;
    height: 12px;
  }

  .timeline-card-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
  }
  .timeline-card-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .timeline-card-meta svg {
    width: 14px;
    height: 14px;
    opacity: 0.5;
  }

  /* Description */
  .timeline-description {
    font-size: 14.5px;
    line-height: 1.7;
    color: var(--text-primary);
    margin-bottom: 16px;
  }

  /* Footer */
  .timeline-footer {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    padding-top: 14px;
    border-top: 1px solid var(--border);
  }

  .timeline-emotion,
  .timeline-cliffhanger,
  .timeline-countdown {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
  }
  .timeline-emotion svg,
  .timeline-cliffhanger svg,
  .timeline-countdown svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
  }

  .timeline-emotion {
    background: var(--bg-secondary);
    color: var(--text-muted);
  }

  .timeline-cliffhanger {
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.15);
    color: #D97706;
  }

  .timeline-countdown {
    background: var(--accent-bg);
    border: 1px solid rgba(29,155,240,0.15);
    color: var(--accent);
    margin-left: auto;
  }

  /* Locked State */
  .timeline-event.locked .timeline-card {
    opacity: 0.6;
  }
  .timeline-event.locked .timeline-card h3 {
    color: var(--text-muted);
  }
  .timeline-event.locked .timeline-description {
    color: var(--text-secondary);
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .timeline-page {
      padding: 0 14px 80px;
    }
    
    .timeline-arc {
      padding: 20px;
      gap: 14px;
      border-radius: 16px;
    }
    
    .timeline-arc-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
    }
    .timeline-arc-icon svg {
      width: 24px;
      height: 24px;
    }
    
    .timeline-arc-content h2 {
      font-size: 18px;
    }
    
    /* Loading mobile */
    .timeline-loading {
      padding: 40px 20px;
    }
    
    .timeline-loading-node {
      width: 40px;
      height: 40px;
    }
    
    .timeline-loading-dot {
      width: 14px;
      height: 14px;
    }
    
    .timeline-loading-node.secondary .timeline-loading-dot {
      width: 10px;
      height: 10px;
    }
    
    .timeline-loading-node.tertiary .timeline-loading-dot {
      width: 8px;
      height: 8px;
    }
    
    .timeline-loading-line {
      height: 36px;
    }
    
    .timeline-loading-text h3 {
      font-size: 18px;
    }
    
    .timeline-loading-text p {
      font-size: 13px;
    }
    
    .timeline-track {
      padding-left: 16px;
    }
    
    .timeline-line-bg {
      left: 31px;
    }
    
    .timeline-event {
      gap: 16px;
      padding-bottom: 24px;
    }
    
    .timeline-node {
      width: 28px;
      height: 28px;
      margin-top: 16px;
    }
    
    .timeline-node-dot {
      width: 28px;
      height: 28px;
    }
    .timeline-node-dot svg {
      width: 12px;
      height: 12px;
    }
    
    .timeline-card {
      padding: 18px;
      border-radius: 16px;
    }
    
    .timeline-card h3 {
      font-size: 16px;
    }
    
    .timeline-beat,
    .timeline-unlocked-badge {
      font-size: 10px;
      padding: 3px 10px;
    }
    
    .timeline-description {
      font-size: 13.5px;
    }
    
    .timeline-footer {
      gap: 8px;
    }
    
    .timeline-emotion,
    .timeline-cliffhanger,
    .timeline-countdown {
      font-size: 11px;
      padding: 5px 10px;
    }
    
    .timeline-countdown {
      margin-left: 0;
      width: 100%;
      justify-content: center;
    }
  }

  @media (max-width: 480px) {
    .timeline-page {
      padding: 0 12px 70px;
    }
    
    .timeline-arc {
      padding: 16px;
      flex-direction: column;
      gap: 12px;
    }
    
    .timeline-arc-icon {
      width: 44px;
      height: 44px;
    }
    
    .timeline-loading {
      padding: 32px 16px;
    }
    
    .timeline-loading-text h3 {
      font-size: 16px;
    }
    
    .timeline-track {
      padding-left: 12px;
    }
    
    .timeline-line-bg {
      left: 27px;
    }
    
    .timeline-event {
      gap: 12px;
    }
    
    .timeline-node {
      width: 24px;
      height: 24px;
    }
    
    .timeline-node-dot {
      width: 24px;
      height: 24px;
    }
    
    .timeline-card {
      padding: 16px;
      border-radius: 14px;
    }
    
    .timeline-card-title-wrap {
      gap: 8px;
    }
    
    .timeline-card h3 {
      font-size: 15px;
    }
    
    .timeline-card-meta {
      gap: 10px;
    }
    
    .timeline-description {
      font-size: 13px;
      line-height: 1.6;
    }
  }
</style>
@endsection