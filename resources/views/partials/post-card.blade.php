{{-- @php
  $event = $post->event ?? ($story->getEvent($post->timeline_event_sequence) ?? null);
  $beat = $event['beat'] ?? ($post->meta['beat'] ?? 'exposition');
  $emotion = $event['emotional_register'] ?? ($post->meta['emotional_register'] ?? null);
  $isBreaking = ($post->sequence === optional($story->posts()->where('timeline_event_sequence', $post->timeline_event_sequence)->orderBy('sequence')->first())->sequence);
@endphp
<article class="post-card beat-{{ $beat }}" style="padding:16px 18px;border-bottom:1px solid var(--border);">
  <div style="display:flex;gap:12px;align-items:flex-start;">
    <div style="width:42px;height:42px;border-radius:14px;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--accent);flex-shrink:0;">
      {{ strtoupper(substr($post->agent->name ?? '?',0,1)) }}
    </div>
    <div style="flex:1;min-width:0;display:grid;gap:8px;">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <a href="{{ route('agents.show', [$story, $post->agent]) }}" style="font-weight:700;color:var(--text-primary);">{{ $post->agent->name }}</a>
        @if($post->agent->role)<span style="font-size:12px;color:var(--text-muted);">{{ $post->agent->role }}</span>@endif
        @if($isBreaking)<span class="breaking-chip">Breaking</span>@endif
        @if($emotion)<span style="font-size:11px;color:var(--text-muted);">{{ str_replace('_',' ', $emotion) }}</span>@endif
      </div>
      <div style="font-size:14px;color:var(--text-primary);line-height:1.6;white-space:pre-wrap;">{{ $post->content }}</div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--text-muted);">
        <span>{{ $post->historical_date }}</span>
        @if($event)<span>{{ $event['title'] }}</span>@endif
        <a href="{{ route('posts.show', [$story, $post]) }}" style="color:var(--accent);font-weight:600;">Open thread</a>
        <a href="{{ route('research.show', [$story, 'q' => $event['title'] ?? $post->agent->name, 'date' => $post->historical_date]) }}" style="color:var(--accent);font-weight:600;">Research</a>
      </div>
      @if($post->replies->count())
        <div class="post-thread">
          @foreach($post->replies as $reply)
            <div class="reply-block">
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:4px;">
                <a href="{{ route('agents.show', [$story, $reply->agent]) }}" style="font-size:13px;font-weight:700;">{{ $reply->agent->name }}</a>
                <span style="font-size:11px;color:var(--text-muted);">reply</span>
              </div>
              <div style="font-size:13px;line-height:1.55;color:var(--text-primary);">{{ $reply->content }}</div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</article> --}}
@php
  $event = $post->event ?? ($story->getEvent($post->timeline_event_sequence) ?? null);
  $beat = $event['beat'] ?? ($post->meta['beat'] ?? 'exposition');
  $emotion = $event['emotional_register'] ?? ($post->meta['emotional_register'] ?? null);
  $isBreaking = ($post->sequence === optional($story->posts()->where('timeline_event_sequence', $post->timeline_event_sequence)->orderBy('sequence')->first())->sequence);
  
  $beatColors = [
    'exposition' => ['border' => '#1D9BF0', 'bg' => 'rgba(29,155,240,0.08)', 'text' => '#1D9BF0'],
    'inciting' => ['border' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.08)', 'text' => '#D97706'],
    'rising' => ['border' => '#3B82F6', 'bg' => 'rgba(59,130,246,0.08)', 'text' => '#2563EB'],
    'crisis' => ['border' => '#EF4444', 'bg' => 'rgba(239,68,68,0.08)', 'text' => '#DC2626'],
    'climax' => ['border' => '#10B981', 'bg' => 'rgba(16,185,129,0.08)', 'text' => '#059669'],
  ];
  $colors = $beatColors[$beat] ?? $beatColors['exposition'];
@endphp

<<article class="post-card beat-{{ $beat }}" data-beat="{{ $beat }}">
  <div class="post-card-accent" style="background: {{ $colors['border'] }};"></div>
  
  <div class="post-card-inner">
    <!-- Avatar -->
    <div class="post-avatar">
      {{ strtoupper(substr($post->agent->name ?? '?',0,1)) }}
    </div>

    <!-- Content -->
    <div class="post-content-wrap">
      <!-- Header -->
      <div class="post-header">
        <div class="post-meta">
          <a href="{{ route('agents.show', [$story, $post->agent]) }}" class="post-author">
            {{ $post->agent->name }}
          </a>
          @if($post->agent->role)
            <span class="post-role">{{ $post->agent->role }}</span>
          @endif
        </div>
        <div class="post-badges">
          @if($isBreaking)
            <span class="post-badge breaking">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
              </svg>
              Breaking
            </span>
          @endif
          @if($emotion)
            <span class="post-badge emotion" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
              <span class="emotion-dot" style="background: {{ $colors['border'] }};"></span>
              {{ str_replace('_',' ', $emotion) }}
            </span>
          @endif
        </div>
      </div>

      <!-- Body -->
      <div class="post-body">
        {{ $post->content }}
      </div>

      <!-- Footer -->
      <div class="post-footer">
        <div class="post-meta-row">
          <span class="post-date">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            {{ $post->historical_date }}
          </span>
          @if($event)
            <span class="post-event">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
              {{ $event['title'] }}
            </span>
          @endif
        </div>
        <div class="post-actions">
          <a href="{{ route('posts.show', [$story, $post]) }}" class="post-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Thread
          </a>
          <a href="{{ route('research.show', [$story, 'q' => $event['title'] ?? $post->agent->name, 'date' => $post->historical_date]) }}" class="post-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Research
          </a>
        </div>
      </div>

      <!-- Replies -->
      @if($post->replies->count())
        <div class="post-replies">
          <div class="replies-divider">
            <span>{{ $post->replies->count() }} {{ Str::plural('reply', $post->replies->count()) }}</span>
          </div>
          @foreach($post->replies as $reply)
            <div class="reply-card">
              <div class="reply-avatar">
                {{ strtoupper(substr($reply->agent->name ?? '?',0,1)) }}
              </div>
              <div class="reply-body">
                <div class="reply-header">
                  <a href="{{ route('agents.show', [$story, $reply->agent]) }}" class="reply-author">
                    {{ $reply->agent->name }}
                  </a>
                  <span class="reply-label">replied</span>
                </div>
                <div class="reply-text">
                  {{ $reply->content }}
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</article>

<style>
  /* ── Post Card Styles ───────────────────────────────────────── */
  .post-card {
    position: relative;
    border-bottom: 1px solid var(--border);
    transition: background 0.15s ease;
    overflow: hidden;
  }
  .post-card:hover {
    background: var(--surface-hover);
  }

  /* Left accent bar */
  .post-card-accent {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    transition: width 0.2s ease;
  }
  .post-card:hover .post-card-accent {
    width: 4px;
  }

  .post-card-inner {
    display: flex;
    gap: 14px;
    padding: 18px 20px 16px;
    position: relative;
  }

  /* Avatar */
  .post-avatar {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(29,155,240,0.2);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .post-card:hover .post-avatar {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(29,155,240,0.3);
  }

  /* Content wrapper */
  .post-content-wrap {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  /* Header */
  .post-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .post-meta {
    display: flex;
    align-items: baseline;
    gap: 8px;
    flex-wrap: wrap;
    min-width: 0;
  }

  .post-author {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.15s;
  }
  .post-author:hover {
    color: var(--accent);
    text-decoration: underline;
    text-underline-offset: 2px;
  }

  .post-role {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
  }

  .post-badges {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
    flex-wrap: wrap;
  }

  .post-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }

  .post-badge.breaking {
    background: var(--yellow-soft);
    color: #6a6500;
  }
  .post-badge.breaking svg {
    width: 12px;
    height: 12px;
  }

  .post-badge.emotion {
    border: 1px solid currentColor;
    opacity: 0.85;
  }
  .emotion-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  /* Body */
  .post-body {
    font-size: 15px;
    line-height: 1.65;
    color: var(--text-primary);
    white-space: pre-wrap;
    word-break: break-word;
    overflow-wrap: break-word;
  }

  /* Footer */
  .post-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding-top: 4px;
  }

  .post-meta-row {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
  }

  .post-date, .post-event {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
  }
  .post-date svg, .post-event svg {
    width: 14px;
    height: 14px;
    opacity: 0.6;
  }

  .post-actions {
    display: flex;
    gap: 4px;
  }

  .post-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    color: var(--accent);
    text-decoration: none;
    transition: all 0.15s;
  }
  .post-action:hover {
    background: var(--accent-bg);
  }
  .post-action svg {
    width: 14px;
    height: 14px;
  }

  /* Replies */
  .post-replies {
    margin-top: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
  }

  .replies-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .replies-divider::before,
  .replies-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }

  .reply-card {
    display: flex;
    gap: 12px;
    padding: 10px 0;
  }
  .reply-card:not(:last-child) {
    border-bottom: 1px solid var(--border);
  }

  .reply-avatar {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    flex-shrink: 0;
    border: 1.5px solid var(--border);
  }

  .reply-body {
    flex: 1;
    min-width: 0;
  }

  .reply-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 3px;
  }

  .reply-author {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    text-decoration: none;
  }
  .reply-author:hover {
    color: var(--accent);
    text-decoration: underline;
    text-underline-offset: 2px;
  }

  .reply-label {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
  }

  .reply-text {
    font-size: 13.5px;
    line-height: 1.55;
    color: var(--text-primary);
    word-break: break-word;
    overflow-wrap: break-word;
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .post-card-inner {
      padding: 14px 14px 12px;
      gap: 12px;
    }
    
    .post-avatar {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      font-size: 14px;
    }
    
    .post-header {
      gap: 8px;
    }
    
    .post-author {
      font-size: 14px;
    }
    
    .post-role {
      font-size: 12px;
    }
    
    .post-badge {
      padding: 3px 8px;
      font-size: 10px;
    }
    
    .post-body {
      font-size: 14px;
      line-height: 1.6;
    }
    
    .post-footer {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }
    
    .post-meta-row {
      gap: 10px;
    }
    
    .reply-card {
      gap: 10px;
    }
    
    .reply-avatar {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      font-size: 11px;
    }
    
    .reply-text {
      font-size: 13px;
    }
  }

  @media (max-width: 480px) {
    .post-card-inner {
      padding: 12px 12px 10px;
    }
    
    .post-avatar {
      width: 36px;
      height: 36px;
      border-radius: 10px;
    }
    
    .post-badges {
      width: 100%;
    }
    
    .post-badge {
      font-size: 9px;
      padding: 2px 6px;
    }
    
    .post-action {
      padding: 5px 10px;
      font-size: 11px;
    }
  }
</style>