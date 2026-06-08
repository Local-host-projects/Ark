@extends('layouts.app')
@php($title='ARK — Thread')
@php($heading='Thread')
@php($subtitle=$story->title)
@section('content')

<div class="thread-page">
  <!-- Event Context Banner -->
  @if($event)
    <div class="thread-event-banner">
      <div class="thread-event-content">
        <div class="thread-event-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
        </div>
        <div class="thread-event-details">
          <div class="thread-event-title">{{ $event['title'] }}</div>
          <div class="thread-event-meta">
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
              {{ $event['date'] }}
            </span>
            <span class="thread-event-beat beat-{{ $event['beat'] ?? 'exposition' }}">
              <span class="beat-dot"></span>
              {{ str_replace('_',' ', $event['beat'] ?? 'scene') }}
            </span>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Main Post -->
  <div class="thread-main">
    @include('partials.post-card', ['post' => $post, 'story' => $story])
  </div>

  <!-- Thread Actions -->
  <div class="thread-actions">
    <a href="{{ route('feed.show', $story) }}" class="thread-back">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 19 5 12 12 5"/>
      </svg>
      Back to feed
    </a>
    <a href="{{ route('research.show', [$story, 'q' => $event['title'] ?? $post->agent->name, 'date' => $post->historical_date]) }}" class="thread-research">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      Research this
    </a>
  </div>
</div>

<style>
  /* ── Thread Page Styles ─────────────────────────────────────── */
  .thread-page {
    padding-bottom: 100px;
  }

  /* Event Banner */
  .thread-event-banner {
    padding: 16px 20px;
    background: linear-gradient(135deg, var(--accent-bg) 0%, var(--bg-secondary) 100%);
    border-bottom: 1px solid var(--border);
    position: relative;
    overflow: hidden;
  }
  .thread-event-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -5%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(29,155,240,0.08) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .thread-event-content {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
    z-index: 1;
  }

  .thread-event-icon {
    width: 44px;
    height: 44px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
  }
  .thread-event-icon svg {
    width: 22px;
    height: 22px;
  }

  .thread-event-details {
    flex: 1;
    min-width: 0;
  }

  .thread-event-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    margin-bottom: 6px;
    line-height: 1.3;
  }

  .thread-event-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .thread-event-meta > span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .thread-event-meta > span svg {
    width: 14px;
    height: 14px;
    opacity: 0.6;
  }

  .thread-event-beat {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .beat-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .thread-event-beat.beat-exposition {
    background: rgba(29,155,240,0.08);
    color: #1D9BF0;
  }
  .thread-event-beat.beat-exposition .beat-dot {
    background: #1D9BF0;
  }

  .thread-event-beat.beat-inciting {
    background: rgba(245,158,11,0.08);
    color: #D97706;
  }
  .thread-event-beat.beat-inciting .beat-dot {
    background: #F59E0B;
  }

  .thread-event-beat.beat-rising {
    background: rgba(59,130,246,0.08);
    color: #2563EB;
  }
  .thread-event-beat.beat-rising .beat-dot {
    background: #3B82F6;
  }

  .thread-event-beat.beat-crisis {
    background: rgba(239,68,68,0.08);
    color: #DC2626;
  }
  .thread-event-beat.beat-crisis .beat-dot {
    background: #EF4444;
  }

  .thread-event-beat.beat-climax {
    background: rgba(16,185,129,0.08);
    color: #059669;
  }
  .thread-event-beat.beat-climax .beat-dot {
    background: #10B981;
  }

  /* Main Post */
  .thread-main {
    border-bottom: 1px solid var(--border);
  }

  /* Thread Actions */
  .thread-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px;
    flex-wrap: wrap;
  }

  .thread-back,
  .thread-research {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
  }
  .thread-back svg,
  .thread-research svg {
    width: 16px;
    height: 16px;
  }

  .thread-back {
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text-secondary);
  }
  .thread-back:hover {
    background: var(--surface-hover);
    border-color: var(--border-strong);
    color: var(--text-primary);
    transform: translateX(-2px);
  }

  .thread-research {
    background: var(--accent);
    color: white;
    border: 1px solid var(--accent);
  }
  .thread-research:hover {
    background: var(--accent-dark);
    transform: translateX(2px);
    box-shadow: 0 4px 16px rgba(29,155,240,0.3);
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .thread-event-banner {
      padding: 14px 16px;
    }
    
    .thread-event-content {
      gap: 12px;
    }
    
    .thread-event-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
    }
    .thread-event-icon svg {
      width: 20px;
      height: 20px;
    }
    
    .thread-event-title {
      font-size: 15px;
    }
    
    .thread-event-meta {
      gap: 8px;
    }
    
    .thread-actions {
      padding: 14px 16px;
      flex-direction: column;
      align-items: stretch;
    }
    
    .thread-back,
    .thread-research {
      justify-content: center;
      padding: 12px 18px;
    }
  }

  @media (max-width: 480px) {
    .thread-event-banner {
      padding: 12px 14px;
    }
    
    .thread-event-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
    }
    
    .thread-event-title {
      font-size: 14px;
    }
    
    .thread-event-beat {
      font-size: 10px;
      padding: 3px 8px;
    }
    
    .beat-dot {
      width: 6px;
      height: 6px;
    }
  }
</style>
@endsection