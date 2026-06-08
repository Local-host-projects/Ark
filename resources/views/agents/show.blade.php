@extends('layouts.app')
@php($title='ARK — '.$agent->name)
@php($heading=$agent->name)
@php($subtitle=trim(($agent->role ?: 'Unknown role').' · '.($agent->affiliation ?: 'Unknown affiliation'), ' ·'))
@section('header-actions')
  <a class="profile-research-btn" href="{{ route('research.show', [$story, 'q' => $agent->name]) }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"/>
      <line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    Research
  </a>
@endsection
@section('content')

<div class="agent-profile-page">
  <!-- Profile Header -->
  <div class="profile-hero">
    <div class="profile-banner"></div>
    <div class="profile-header-content">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar">
          {{ strtoupper(substr($agent->name,0,1)) }}
        </div>
      </div>
      <div class="profile-info">
        <h1 class="profile-name">{{ $agent->name }}</h1>
        <div class="profile-role">{{ $agent->role }}</div>
        <div class="profile-affiliation">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          {{ $agent->affiliation ?: 'Unknown affiliation' }}
        </div>
      </div>
    </div>
  </div>

  <!-- Bio & Stats -->
  <div class="profile-body">
    <div class="profile-bio-card">
      <p class="profile-bio-text">{{ $agent->background }}</p>
      
      <div class="profile-chips">
        @foreach(($agent->goals ?? []) as $goal)
          <span class="profile-chip goal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            {{ $goal }}
          </span>
        @endforeach
        @foreach(($agent->concerns ?? []) as $concern)
          <span class="profile-chip concern">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ $concern }}
          </span>
        @endforeach
      </div>
    </div>

    <!-- Posts Feed -->
    <div class="profile-posts-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
      </svg>
      <span>Posts by {{ $agent->name }}</span>
      <div class="profile-posts-count">{{ count($posts) }}</div>
    </div>

    <div class="profile-posts">
      @forelse($posts as $post)
        @include('partials.post-card', ['post' => $post, 'story' => $story])
      @empty
        <div class="profile-empty">
          <div class="profile-empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
          </div>
          <h3>No posts yet</h3>
          <p>This agent hasn't made any posts yet. Check back after the next timeline event.</p>
        </div>
      @endforelse
    </div>

    <div class="profile-pagination">
      {{ $posts->links() }}
    </div>
  </div>
</div>

<style>
  /* ── Agent Profile Page Styles ────────────────────────────────── */
  .agent-profile-page {
    padding-bottom: 100px;
  }

  /* Research Button (Header Action) */
  .profile-research-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: var(--surface);
    border: 1px solid var(--border-strong);
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    text-decoration: none;
    transition: all 0.15s ease;
  }
  .profile-research-btn:hover {
    background: var(--accent-bg);
    border-color: var(--accent);
    color: var(--accent);
  }
  .profile-research-btn svg {
    width: 16px;
    height: 16px;
  }

  /* Hero Banner */
  .profile-hero {
    position: relative;
    border-bottom: 1px solid var(--border);
  }

  .profile-banner {
    height: 140px;
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 50%, var(--green) 100%);
    position: relative;
    overflow: hidden;
  }
  .profile-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  }
  .profile-banner::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: linear-gradient(to top, var(--bg), transparent);
  }

  .profile-header-content {
    padding: 0 24px 20px;
    display: flex;
    gap: 20px;
    align-items: flex-end;
    margin-top: -40px;
    position: relative;
    z-index: 1;
  }

  /* Avatar */
  .profile-avatar-wrap {
    flex-shrink: 0;
  }

  .profile-avatar {
    width: 84px;
    height: 84px;
    border-radius: 24px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display, 'DM Serif Display', Georgia, serif);
    font-size: 36px;
    font-weight: 400;
    color: white;
    border: 4px solid var(--bg);
    box-shadow: 0 8px 32px rgba(29,155,240,0.25);
  }

  /* Info */
  .profile-info {
    flex: 1;
    min-width: 0;
    padding-bottom: 4px;
  }

  .profile-name {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.4px;
    line-height: 1.2;
    margin-bottom: 4px;
  }

  .profile-role {
    font-size: 15px;
    color: var(--text-secondary);
    font-weight: 500;
    margin-bottom: 8px;
  }

  .profile-affiliation {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: var(--accent-bg);
    border: 1px solid rgba(29,155,240,0.15);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: var(--accent);
  }
  .profile-affiliation svg {
    width: 14px;
    height: 14px;
  }

  /* Body */
  .profile-body {
    padding: 0 20px;
  }

  /* Bio Card */
  .profile-bio-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    margin: 20px 0;
    box-shadow: var(--shadow-sm);
  }

  .profile-bio-text {
    font-size: 15px;
    line-height: 1.7;
    color: var(--text-primary);
    margin-bottom: 20px;
  }

  /* Chips */
  .profile-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .profile-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.15s ease;
  }
  .profile-chip:hover {
    transform: translateY(-1px);
  }
  .profile-chip svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
  }

  .profile-chip.goal {
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.2);
    color: #059669;
  }
  .profile-chip.goal:hover {
    background: rgba(16,185,129,0.12);
    box-shadow: 0 2px 8px rgba(16,185,129,0.1);
  }

  .profile-chip.concern {
    background: rgba(239,68,68,0.06);
    border: 1px solid rgba(239,68,68,0.15);
    color: #DC2626;
  }
  .profile-chip.concern:hover {
    background: rgba(239,68,68,0.1);
    box-shadow: 0 2px 8px rgba(239,68,68,0.08);
  }

  /* Posts Header */
  .profile-posts-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 4px;
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border);
    margin-bottom: 8px;
  }
  .profile-posts-header svg {
    width: 20px;
    height: 20px;
    color: var(--accent);
  }
  .profile-posts-count {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 10px;
    background: var(--bg-secondary);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    color: var(--text-muted);
  }

  /* Posts Feed */
  .profile-posts {
    min-height: 100px;
  }

  /* Empty State */
  .profile-empty {
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
    margin: 12px 0;
  }
  .profile-empty-icon {
    width: 64px;
    height: 64px;
    background: var(--bg-secondary);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
  }
  .profile-empty-icon svg {
    width: 28px;
    height: 28px;
  }
  .profile-empty h3 {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-primary);
  }
  .profile-empty p {
    font-size: 14px;
    color: var(--text-muted);
    max-width: 320px;
    line-height: 1.6;
  }

  /* Pagination */
  .profile-pagination {
    padding: 20px 0;
    display: flex;
    justify-content: center;
  }
  .profile-pagination nav {
    display: flex;
    gap: 6px;
  }
  .profile-pagination .page-item {
    list-style: none;
  }
  .profile-pagination .page-link {
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
  .profile-pagination .page-link:hover {
    border-color: var(--border-strong);
    background: var(--surface-hover);
  }
  .profile-pagination .page-item.active .page-link {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
  }
  .profile-pagination .page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .profile-banner {
      height: 100px;
    }
    
    .profile-header-content {
      padding: 0 16px 16px;
      gap: 14px;
      margin-top: -30px;
    }
    
    .profile-avatar {
      width: 64px;
      height: 64px;
      border-radius: 18px;
      font-size: 28px;
      border-width: 3px;
    }
    
    .profile-name {
      font-size: 20px;
    }
    
    .profile-role {
      font-size: 13px;
    }
    
    .profile-affiliation {
      font-size: 11px;
      padding: 4px 10px;
    }
    
    .profile-body {
      padding: 0 14px;
    }
    
    .profile-bio-card {
      padding: 18px;
      margin: 14px 0;
      border-radius: 16px;
    }
    
    .profile-bio-text {
      font-size: 14px;
      margin-bottom: 16px;
    }
    
    .profile-chip {
      padding: 6px 12px;
      font-size: 12px;
    }
    
    .profile-posts-header {
      padding: 12px 4px;
      font-size: 14px;
    }
    
    .profile-empty {
      padding: 48px 20px;
    }
    
    .profile-empty-icon {
      width: 52px;
      height: 52px;
      border-radius: 16px;
    }
    
    .profile-pagination {
      padding: 16px 0;
    }
  }

  @media (max-width: 480px) {
    .profile-banner {
      height: 80px;
    }
    
    .profile-header-content {
      margin-top: -24px;
      gap: 12px;
    }
    
    .profile-avatar {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      font-size: 24px;
    }
    
    .profile-name {
      font-size: 18px;
    }
    
    .profile-chips {
      gap: 8px;
    }
    
    .profile-chip {
      padding: 5px 10px;
      font-size: 11px;
    }
    .profile-chip svg {
      width: 14px;
      height: 14px;
    }
  }
</style>
@endsection