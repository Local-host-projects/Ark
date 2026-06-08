@extends('layouts.app')
@php($title='ARK — Agents')
@php($heading='Agents')
@php($subtitle=$story->title)
@section('content')

<div class="agents-page">
  <!-- Section Header -->
  <div class="agents-header">
    <div class="agents-header-content">
      <div class="agents-count">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <span>{{ count($agents) }} {{ Str::plural('agent', count($agents)) }} in this story</span>
      </div>
    </div>
  </div>

  <!-- Agents Grid -->
  <section class="agents-grid">
    @foreach($agents as $index => $agent)
      @php
        $gradients = [
          ['from' => '#1D9BF0', 'to' => '#0EA5E9'],
          ['from' => '#F59E0B', 'to' => '#D97706'],
          ['from' => '#10B981', 'to' => '#059669'],
          ['from' => '#8B5CF6', 'to' => '#7C3AED'],
          ['from' => '#EC4899', 'to' => '#DB2777'],
          ['from' => '#06B6D4', 'to' => '#0891B2'],
        ];
        $grad = $gradients[$index % count($gradients)];
      @endphp
      <article class="agent-card">
        <div class="agent-card-accent" style="background: linear-gradient(135deg, {{ $grad['from'] }}, {{ $grad['to'] }});"></div>
        <div class="agent-card-inner">
          <div class="agent-avatar-wrap">
            <div class="agent-avatar" style="background: linear-gradient(135deg, {{ $grad['from'] }}, {{ $grad['to'] }});">
              {{ strtoupper(substr($agent->name,0,1)) }}
            </div>
            <div class="agent-avatar-ring" style="border-color: {{ $grad['from'] }}33;"></div>
          </div>
          
          <div class="agent-info">
            <div class="agent-name-row">
              <h3 class="agent-name">{{ $agent->name }}</h3>
              <span class="agent-type">{{ ucfirst($agent->type) }}</span>
            </div>
            <div class="agent-role">{{ $agent->role }} · {{ $agent->affiliation }}</div>
            <p class="agent-bio">{{ $agent->background }}</p>
            
            <div class="agent-metrics">
              <div class="agent-metric">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
                <strong>{{ $agent->posts_count }}</strong>
                <span>posts</span>
              </div>
              <div class="agent-metric">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                </svg>
                <strong>{{ ucfirst($agent->type) }}</strong>
                <span>type</span>
              </div>
            </div>
            
            <a class="agent-btn" href="{{ route('agents.show', [$story, $agent]) }}">
              <span>Open profile</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
              </svg>
            </a>
          </div>
        </div>
      </article>
    @endforeach
  </section>
</div>

<style>
  /* ── Agents Page Styles ─────────────────────────────────────── */
  .agents-page {
    padding: 0 20px 100px;
  }

  .agents-header {
    padding: 20px 0 16px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
  }

  .agents-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
  }

  .agents-count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
  }
  .agents-count svg {
    width: 18px;
    height: 18px;
    color: var(--accent);
  }

  /* Grid */
  .agents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
  }

  /* Agent Card */
  .agent-card {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .agent-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-strong);
  }

  .agent-card-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    transition: height 0.25s ease;
  }
  .agent-card:hover .agent-card-accent {
    height: 6px;
  }

  .agent-card-inner {
    padding: 24px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
  }

  /* Avatar */
  .agent-avatar-wrap {
    position: relative;
    flex-shrink: 0;
  }

  .agent-avatar {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 20px;
    color: white;
    position: relative;
    z-index: 1;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transition: transform 0.2s ease;
  }
  .agent-card:hover .agent-avatar {
    transform: scale(1.08) rotate(-2deg);
  }

  .agent-avatar-ring {
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 20px;
    border: 2px solid transparent;
    transition: all 0.25s ease;
    opacity: 0;
  }
  .agent-card:hover .agent-avatar-ring {
    opacity: 1;
    transform: scale(1.05);
  }

  /* Info */
  .agent-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .agent-name-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .agent-name {
    font-size: 17px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    line-height: 1.2;
  }

  .agent-type {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .agent-role {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
  }

  .agent-bio {
    font-size: 13.5px;
    line-height: 1.6;
    color: var(--text-secondary);
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Metrics */
  .agent-metrics {
    display: flex;
    gap: 16px;
    margin-top: 4px;
  }

  .agent-metric {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .agent-metric svg {
    width: 14px;
    height: 14px;
    opacity: 0.6;
  }
  .agent-metric strong {
    color: var(--text-primary);
    font-weight: 700;
  }
  .agent-metric span {
    font-size: 11px;
  }

  /* Button */
  .agent-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
    padding: 10px 18px;
    background: var(--accent);
    color: white;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    align-self: flex-start;
  }
  .agent-btn:hover {
    background: var(--accent-dark);
    transform: translateX(2px);
    box-shadow: 0 4px 16px rgba(29,155,240,0.3);
  }
  .agent-btn svg {
    width: 14px;
    height: 14px;
    transition: transform 0.2s ease;
  }
  .agent-btn:hover svg {
    transform: translateX(2px);
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .agents-page {
      padding: 0 14px 80px;
    }
    
    .agents-header {
      padding: 14px 0 12px;
      margin-bottom: 14px;
    }
    
    .agents-grid {
      grid-template-columns: 1fr;
      gap: 12px;
    }
    
    .agent-card-inner {
      padding: 18px;
      gap: 14px;
    }
    
    .agent-avatar {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      font-size: 18px;
    }
    
    .agent-name {
      font-size: 16px;
    }
    
    .agent-bio {
      font-size: 13px;
      -webkit-line-clamp: 2;
    }
    
    .agent-metrics {
      gap: 12px;
    }
    
    .agent-btn {
      width: 100%;
      justify-content: center;
      margin-top: 12px;
    }
  }

  @media (max-width: 480px) {
    .agents-page {
      padding: 0 12px 70px;
    }
    
    .agent-card-inner {
      padding: 16px;
    }
    
    .agent-avatar {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      font-size: 16px;
    }
    
    .agent-name-row {
      gap: 6px;
    }
    
    .agent-type {
      font-size: 10px;
      padding: 2px 8px;
    }
  }
</style>
@endsection