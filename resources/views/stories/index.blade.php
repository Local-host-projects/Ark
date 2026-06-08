@extends('layouts.app')
@section('header-actions')
  @if($stories->count())
    <div class="stories-count-badge">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
      </svg>
      <span>{{ $stories->count() }} {{ Str::plural('story', $stories->count()) }}</span>
    </div>
  @endif
@endsection
@php($title='ARK — Stories')
@php($heading='Stories')
@php($subtitle='Create and manage your simulations')
@section('content')

<div class="stories-page">
  <!-- Creation Form -->
  <section class="stories-create">
    <div class="stories-create-header">
      <div class="stories-create-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </div>
      <div class="stories-create-title">
        <h2>Create a new story</h2>
        <p>Describe a historical event and let ARK bring it to life.</p>
      </div>
    </div>

    <form class="stories-form" method="POST" action="{{ route('stories.store') }}">
      @csrf
      <div class="stories-field stories-field-full">
        <label>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
          Story title
        </label>
        <input type="text" name="title" value="{{ old('title') }}" placeholder="World War II — European theatre" class="stories-input" required>
      </div>

      <div class="stories-field stories-field-full">
        <label>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="8" y1="6" x2="21" y2="6"/>
            <line x1="8" y1="12" x2="21" y2="12"/>
            <line x1="8" y1="18" x2="21" y2="18"/>
            <line x1="3" y1="6" x2="3.01" y2="6"/>
            <line x1="3" y1="12" x2="3.01" y2="12"/>
            <line x1="3" y1="18" x2="3.01" y2="18"/>
          </svg>
          Prompt
        </label>
        <textarea name="prompt" placeholder="Describe the historical event, period, or cultural moment you want ARK to simulate..." class="stories-input stories-textarea" required>{{ old('prompt') }}</textarea>
      </div>

      <div class="stories-field stories-field-full">
        <label>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          Description <span class="stories-optional">optional</span>
        </label>
        <input type="text" name="description" value="{{ old('description') }}" placeholder="Optional short description" class="stories-input">
      </div>

      <div class="stories-form-actions">
        <button type="submit" class="stories-submit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Create story
        </button>
      </div>
    </form>
  </section>

  <!-- Stories List -->
  <section class="stories-list">
    @forelse($stories as $story)
      <article class="story-card">
        <div class="story-card-content">
          <div class="story-card-main">
            <div class="story-card-header">
              <div class="story-title-wrap">
                <h3 class="story-title">{{ $story->title }}</h3>
                <span class="story-status status-{{ $story->status }}">
                  <span class="status-dot"></span>
                  {{ $story->status }}
                </span>
              </div>
              <p class="story-description">{{ $story->description ?: 'No description provided.' }}</p>
            </div>

            <div class="story-metrics">
              <div class="story-metric">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <strong>{{ $story->agents_count }}</strong>
                <span>agents</span>
              </div>
              <div class="story-metric">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
                <strong>{{ $story->posts_count }}</strong>
                <span>posts</span>
              </div>
              <div class="story-metric">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                  <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
                </svg>
                <strong>{{ $story->spaces_count }}</strong>
                <span>spaces</span>
              </div>
              <div class="story-metric">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
                <strong>{{ $story->current_sequence }}/{{ count($story->timeline ?? []) }}</strong>
                <span>events</span>
              </div>
            </div>

            <div class="story-progress">
              <div class="story-progress-track">
                <div class="story-progress-fill" style="width:{{ $story->progressPercent() }}%;"></div>
              </div>
              <span class="story-progress-label">{{ $story->progressPercent() }}% complete</span>
            </div>
          </div>

          <div class="story-card-actions">
            <a href="{{ route('feed.show', $story) }}" class="story-btn story-btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="5 3 19 12 5 21 5 3"/>
              </svg>
              Open feed
            </a>
            <form method="POST" action="{{ route('stories.destroy', $story) }}" onsubmit="return confirm('Delete this story? This cannot be undone.');" class="story-delete-form">
              @csrf @method('DELETE')
              <button type="submit" class="story-btn story-btn-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Delete
              </button>
            </form>
          </div>
        </div>
      </article>
    @empty
      <div class="stories-empty">
        <div class="stories-empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
          </svg>
        </div>
        <h3>No stories yet</h3>
        <p>Create your first story above and start exploring history.</p>
      </div>
    @endforelse
  </section>
</div>

<style>
  /* ── Stories Page Styles ────────────────────────────────────── */
  .stories-page {
    padding: 0 20px 100px;
  }

  /* Header Badge */
  .stories-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: var(--accent-bg);
    border: 1px solid rgba(29,155,240,0.15);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    color: var(--accent);
  }
  .stories-count-badge svg {
    width: 16px;
    height: 16px;
  }

  /* Creation Form */
  .stories-create {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
  }
  .stories-create::before {
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

  .stories-create-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;
    position: relative;
    z-index: 1;
  }

  .stories-create-icon {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(29,155,240,0.25);
  }
  .stories-create-icon svg {
    width: 26px;
    height: 26px;
  }

  .stories-create-title h2 {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    margin-bottom: 4px;
  }
  .stories-create-title p {
    font-size: 14px;
    color: var(--text-muted);
    font-weight: 500;
  }

  /* Form */
  .stories-form {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    position: relative;
    z-index: 1;
  }

  .stories-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1 1 300px;
  }
  .stories-field-full {
    flex: 1 1 100%;
  }

  .stories-field label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .stories-field label svg {
    width: 14px;
    height: 14px;
    opacity: 0.6;
  }

  .stories-optional {
    font-weight: 500;
    color: var(--text-muted);
    opacity: 0.6;
    text-transform: none;
    margin-left: auto;
  }

  .stories-input {
    width: 100%;
    padding: 12px 16px;
    background: var(--bg);
    border: 2px solid var(--border);
    border-radius: 14px;
    font-size: 14px;
    color: var(--text-primary);
    outline: none;
    transition: all 0.2s ease;
  }
  .stories-input:focus {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 4px var(--accent-bg);
  }
  .stories-input::placeholder {
    color: var(--text-muted);
    opacity: 0.5;
  }

  .stories-textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.6;
  }

  .stories-form-actions {
    flex: 1 1 100%;
    display: flex;
    justify-content: flex-end;
    margin-top: 4px;
  }

  .stories-submit {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 28px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .stories-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(29,155,240,0.35);
  }
  .stories-submit:active {
    transform: translateY(0);
  }
  .stories-submit svg {
    width: 18px;
    height: 18px;
  }

  /* Stories List */
  .stories-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  /* Story Card */
  .story-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow-sm);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  .story-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-strong);
  }
  .story-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--accent), var(--green));
    opacity: 0;
    transition: opacity 0.2s;
  }
  .story-card:hover::before {
    opacity: 1;
  }

  .story-card-content {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
  }

  .story-card-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .story-card-header {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .story-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .story-title {
    font-size: 19px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    line-height: 1.2;
  }

  .story-status {
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

  .story-status.status-active {
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.15);
    color: #059669;
  }
  .story-status.status-active .status-dot {
    background: #10B981;
    box-shadow: 0 0 0 2px rgba(16,185,129,0.2);
  }

  .story-status.status-paused {
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.15);
    color: #D97706;
  }
  .story-status.status-paused .status-dot {
    background: #F59E0B;
  }

  .story-status.status-completed {
    background: rgba(29,155,240,0.08);
    border: 1px solid rgba(29,155,240,0.15);
    color: #1D9BF0;
  }
  .story-status.status-completed .status-dot {
    background: #1D9BF0;
  }

  .story-status.status-draft {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    color: var(--text-muted);
  }
  .story-status.status-draft .status-dot {
    background: var(--text-muted);
  }

  .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .story-description {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Metrics */
  .story-metrics {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
  }

  .story-metric {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .story-metric svg {
    width: 18px;
    height: 18px;
    opacity: 0.5;
  }
  .story-metric strong {
    font-weight: 800;
    color: var(--text-primary);
  }
  .story-metric span {
    font-size: 12px;
  }

  /* Progress */
  .story-progress {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .story-progress-track {
    flex: 1;
    height: 6px;
    background: var(--bg-secondary);
    border-radius: 999px;
    overflow: hidden;
    max-width: 200px;
  }

  .story-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent), var(--green));
    border-radius: 999px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }
  .story-progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 20px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3));
    border-radius: 0 999px 999px 0;
  }

  .story-progress-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    white-space: nowrap;
  }

  /* Actions */
  .story-card-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex-shrink: 0;
  }

  .story-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.15s ease;
    white-space: nowrap;
  }
  .story-btn svg {
    width: 16px;
    height: 16px;
  }

  .story-btn-primary {
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: white;
    box-shadow: 0 4px 16px rgba(29,155,240,0.25);
  }
  .story-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(29,155,240,0.35);
  }

  .story-btn-danger {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-muted);
  }
  .story-btn-danger:hover {
    background: rgba(239,68,68,0.08);
    border-color: rgba(239,68,68,0.2);
    color: #DC2626;
  }

  .story-delete-form {
    display: inline-flex;
  }

  /* Empty State */
  .stories-empty {
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
  .stories-empty-icon {
    width: 64px;
    height: 64px;
    background: var(--bg-secondary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
  }
  .stories-empty-icon svg {
    width: 28px;
    height: 28px;
  }
  .stories-empty h3 {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
  }
  .stories-empty p {
    font-size: 14px;
    color: var(--text-muted);
    max-width: 280px;
    line-height: 1.6;
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .stories-page {
      padding: 0 14px 80px;
    }
    
    .stories-create {
      padding: 20px;
      border-radius: 16px;
    }
    
    .stories-create-icon {
      width: 44px;
      height: 44px;
      border-radius: 14px;
    }
    .stories-create-icon svg {
      width: 22px;
      height: 22px;
    }
    
    .stories-create-title h2 {
      font-size: 18px;
    }
    
    .stories-form {
      flex-direction: column;
      gap: 16px;
    }
    
    .stories-field {
      flex: 1 1 100% !important;
    }
    
    .stories-form-actions {
      justify-content: stretch;
      margin-top: 0;
    }
    
    .stories-submit {
      width: 100%;
      justify-content: center;
      padding: 14px;
    }
    
    .story-card {
      padding: 18px;
      border-radius: 16px;
    }
    
    .story-card-content {
      flex-direction: column;
      gap: 16px;
    }
    
    .story-card-actions {
      flex-direction: row;
      width: 100%;
    }
    
    .story-btn {
      flex: 1;
      justify-content: center;
      padding: 12px;
    }
    
    .story-metrics {
      gap: 16px;
    }
    
    .story-progress-track {
      max-width: none;
    }
    
    .stories-empty {
      padding: 48px 20px;
    }
  }

  @media (max-width: 480px) {
    .stories-page {
      padding: 0 12px 70px;
    }
    
    .stories-create {
      padding: 16px;
    }
    
    .stories-create-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
    }
    
    .stories-create-title h2 {
      font-size: 16px;
    }
    
    .story-title {
      font-size: 17px;
    }
    
    .story-status {
      font-size: 10px;
      padding: 3px 10px;
    }
    
    .story-metric {
      font-size: 12px;
    }
    
    .story-metric svg {
      width: 16px;
      height: 16px;
    }
    
    .story-btn {
      font-size: 12px;
      padding: 10px 16px;
    }
  }
</style>
@endsection