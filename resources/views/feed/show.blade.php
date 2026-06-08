@extends('layouts.app')
@php($title='ARK — '.$story->title)
@php($heading=$story->title)
@php($subtitle=($story->period_start ?: 'Unknown start').' — '.($story->period_end ?: 'Unknown end'))
@section('header-actions')
  <div style="display:flex;flex-direction:column;align-items:flex-end;gap:3px;">
    <div class="feed-progress-badge">{{ $story->progressPercent() }}% complete</div>
    @if($nextEvent && !empty($nextEvent['scheduled_at']))
      <div class="feed-countdown" data-countdown="{{ $nextEvent['scheduled_at'] }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        <span>Loading...</span>
      </div>
    @endif
  </div>
@endsection
@section('content')

<!-- Story Hero -->
<div class="feed-hero">
  <div class="feed-hero-content">
    <div class="feed-hero-stats">
      <div class="feed-stat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <div>
          <strong>{{ $story->agents->count() }}</strong>
          <span>agents</span>
        </div>
      </div>
      <div class="feed-stat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
        <div>
          <strong>{{ $story->posts()->count() }}</strong>
          <span>posts</span>
        </div>
      </div>
      <div class="feed-stat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
        </svg>
        <div>
          <strong>{{ $story->spaces()->count() }}</strong>
          <span>spaces</span>
        </div>
      </div>
      <div class="feed-stat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        <div>
          <strong>{{ $story->current_sequence }}/{{ $story->totalEvents() }}</strong>
          <span>events</span>
        </div>
      </div>
    </div>
    <div class="feed-progress-track">
      <div class="feed-progress-fill" style="width:{{ $story->progressPercent() }}%;"></div>
    </div>
  </div>
</div>

<!-- Posts Feed -->
<div class="feed-posts">
  @forelse($posts as $post)
    @include('partials.post-card', ['post' => $post, 'story' => $story])
  @empty
    <div class="feed-empty">
      <div class="feed-empty-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <h3>No posts yet</h3>
      <p>Your first timeline event will appear as soon as it fires.</p>
    </div>
  @endforelse
</div>

<!-- Pagination -->
<div class="feed-pagination">
  {{ $posts->links() }}
</div>

<style>
  /* ── Feed Page Styles ───────────────────────────────────────── */
  .feed-progress-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: var(--accent-bg);
    border: 1px solid rgba(29,155,240,0.15);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    color: var(--accent);
    letter-spacing: 0.2px;
  }

  .feed-countdown {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .feed-countdown svg {
    width: 14px;
    height: 14px;
    color: var(--accent);
  }

  /* Hero Section */
  .feed-hero {
    padding: 24px 20px 20px;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(180deg, var(--bg-glass-strong) 0%, var(--bg) 100%);
    position: relative;
    overflow: hidden;
  }
  .feed-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(29,155,240,0.06) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .feed-hero-content {
    position: relative;
    z-index: 1;
  }

  .feed-hero-stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }

  .feed-stat {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    transition: all 0.2s ease;
  }
  .feed-stat:hover {
    border-color: var(--border-strong);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
  }
  .feed-stat svg {
    width: 20px;
    height: 20px;
    color: var(--accent);
    flex-shrink: 0;
  }
  .feed-stat strong {
    display: block;
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
  }
  .feed-stat span {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
    text-transform: lowercase;
  }

  .feed-progress-track {
    height: 6px;
    background: var(--bg-secondary);
    border-radius: 999px;
    overflow: hidden;
    position: relative;
  }
  .feed-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent), var(--green));
    border-radius: 999px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }
  .feed-progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 20px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3));
    border-radius: 0 999px 999px 0;
  }

  /* Posts Feed */
  .feed-posts {
    min-height: 200px;
  }

  /* Empty State */
  .feed-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 24px;
    text-align: center;
    gap: 16px;
  }
  .feed-empty-icon {
    width: 64px;
    height: 64px;
    background: var(--bg-secondary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
  }
  .feed-empty-icon svg {
    width: 28px;
    height: 28px;
  }
  .feed-empty h3 {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
  }
  .feed-empty p {
    font-size: 14px;
    color: var(--text-muted);
    max-width: 280px;
    line-height: 1.6;
  }

  /* Pagination */
  .feed-pagination {
    padding: 20px;
    display: flex;
    justify-content: center;
  }
  .feed-pagination nav {
    display: flex;
    gap: 6px;
  }
  .feed-pagination .page-item {
    list-style: none;
  }
  .feed-pagination .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--surface);
    border: 1px solid var(--border);
    text-decoration: none;
    transition: all 0.15s;
  }
  .feed-pagination .page-link:hover {
    border-color: var(--border-strong);
    background: var(--surface-hover);
  }
  .feed-pagination .page-item.active .page-link {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
  }
  .feed-pagination .page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .feed-hero {
      padding: 16px 14px 14px;
    }
    .feed-hero-stats {
      gap: 8px;
      margin-bottom: 12px;
    }
    .feed-stat {
      flex: 1 1 calc(50% - 4px);
      padding: 10px 12px;
      min-width: 0;
    }
    .feed-stat svg {
      width: 18px;
      height: 18px;
    }
    .feed-stat strong {
      font-size: 15px;
    }
    .feed-stat span {
      font-size: 10px;
    }
    .feed-progress-track {
      height: 5px;
    }
    .feed-empty {
      padding: 48px 20px;
    }
    .feed-empty-icon {
      width: 52px;
      height: 52px;
      border-radius: 16px;
    }
    .feed-empty-icon svg {
      width: 24px;
      height: 24px;
    }
    .feed-empty h3 {
      font-size: 16px;
    }
    .feed-pagination {
      padding: 16px 14px;
    }
    .feed-pagination .page-link {
      min-width: 32px;
      height: 32px;
      padding: 0 10px;
      font-size: 12px;
      border-radius: 8px;
    }
  }

  @media (max-width: 480px) {
    .feed-stat {
      flex: 1 1 100%;
      flex-direction: row;
      justify-content: flex-start;
    }
    .feed-hero::before {
      width: 200px;
      height: 200px;
      top: -30%;
    }
  }
</style>
@endsection