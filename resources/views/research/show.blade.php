@extends('layouts.app')
@php($title='ARK — Research')
@php($heading='Research')
@php($subtitle=$story->title)
@section('content')

<div class="research-page">
  <!-- Search Form -->
  <section class="research-search">
    <div class="research-search-header">
      <div class="research-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          <line x1="11" y1="8" x2="11" y2="14"/>
          <line x1="8" y1="11" x2="14" y2="11"/>
        </svg>
      </div>
      <div class="research-search-title">
        <h2>Explore history</h2>
        <p>Search topics, dates, or figures to uncover deeper context.</p>
      </div>
    </div>

    <form class="research-form" method="GET" action="{{ route('research.show', $story) }}">
      <div class="research-field research-field-topic">
        <label>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          Topic
        </label>
        <input type="text" name="q" value="{{ $query }}" placeholder="Pearl Harbor, Churchill, Hiroshima..." class="research-input">
      </div>
      <div class="research-field research-field-date">
        <label>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          Historical date
        </label>
        <input type="text" name="date" placeholder="1941-12-07" value="{{ request('date') }}" class="research-input">
      </div>
      <button type="submit" class="research-submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        Research
      </button>
    </form>
  </section>

  <!-- Results -->
  @if($result)
    <div class="research-results">
      <!-- Summary -->
      <section class="research-card research-summary">
        <div class="research-card-header">
          <div class="research-card-icon" style="background: rgba(29,155,240,0.08); color: #1D9BF0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
          </div>
          <div>
            <h3 class="research-card-title">{{ $result['query'] }}</h3>
            <span class="research-card-subtitle">AI-generated summary</span>
          </div>
        </div>
        <p class="research-summary-text">{{ $result['summary'] }}</p>
      </section>

      <!-- Key Facts -->
      @if(!empty($result['key_facts']))
        <section class="research-card research-facts">
          <div class="research-card-header">
            <div class="research-card-icon" style="background: rgba(16,185,129,0.08); color: #059669;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <h3 class="research-card-title">Key facts</h3>
          </div>
          <ul class="research-list">
            @foreach($result['key_facts'] as $fact)
              <li class="research-list-item">
                <span class="research-bullet"></span>
                <span class="research-list-text">{{ $fact }}</span>
              </li>
            @endforeach
          </ul>
        </section>
      @endif

      <!-- Deeper Questions -->
      @if(!empty($result['deeper_questions']))
        <section class="research-card research-questions">
          <div class="research-card-header">
            <div class="research-card-icon" style="background: rgba(245,158,11,0.08); color: #D97706;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <h3 class="research-card-title">Deeper questions</h3>
          </div>
          <ul class="research-list research-list-questions">
            @foreach($result['deeper_questions'] as $question)
              <li class="research-list-item">
                <span class="research-bullet research-bullet-question"></span>
                <span class="research-list-text">{{ $question }}</span>
              </li>
            @endforeach
          </ul>
        </section>
      @endif

      <!-- Sources -->
      @if(!empty($result['sources']))
        <section class="research-card research-sources">
          <div class="research-card-header">
            <div class="research-card-icon" style="background: rgba(139,92,246,0.08); color: #7C3AED;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15 3 21 3 21 9"/>
                <line x1="10" y1="14" x2="21" y2="3"/>
              </svg>
            </div>
            <h3 class="research-card-title">Sources</h3>
          </div>
          <ul class="research-source-list">
            @foreach($result['sources'] as $source)
              <li>
                <a href="{{ $source['url'] }}" target="_blank" rel="noopener" class="research-source-link">
                  <span class="research-source-title">{{ $source['title'] }}</span>
                  <span class="research-source-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="5" y1="12" x2="19" y2="12"/>
                      <polyline points="12 5 19 12 12 19"/>
                    </svg>
                  </span>
                </a>
              </li>
            @endforeach
          </ul>
        </section>
      @endif
    </div>
  @endif
</div>

<style>
  /* ── Research Page Styles ───────────────────────────────────── */
  .research-page {
    padding: 0 20px 100px;
  }

  /* Search Section */
  .research-search {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
  }

  .research-search-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;
  }

  .research-icon {
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
  .research-icon svg {
    width: 26px;
    height: 26px;
  }

  .research-search-title h2 {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    margin-bottom: 4px;
  }
  .research-search-title p {
    font-size: 14px;
    color: var(--text-muted);
    font-weight: 500;
  }

  /* Form */
  .research-form {
    display: flex;
    gap: 14px;
    align-items: flex-end;
    flex-wrap: wrap;
  }

  .research-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1 1 280px;
  }
  .research-field-topic {
    flex: 2 1 320px;
  }
  .research-field-date {
    flex: 1 1 180px;
  }

  .research-field label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .research-field label svg {
    width: 14px;
    height: 14px;
    opacity: 0.6;
  }

  .research-input {
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
  .research-input:focus {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 4px var(--accent-bg);
  }
  .research-input::placeholder {
    color: var(--text-muted);
    opacity: 0.5;
  }

  .research-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    height: fit-content;
  }
  .research-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(29,155,240,0.35);
  }
  .research-submit:active {
    transform: translateY(0);
  }
  .research-submit svg {
    width: 18px;
    height: 18px;
  }

  /* Results */
  .research-results {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .research-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
  }
  .research-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }

  .research-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
  }

  .research-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .research-card-icon svg {
    width: 20px;
    height: 20px;
  }

  .research-card-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
    line-height: 1.2;
  }
  .research-card-subtitle {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
    margin-top: 2px;
    display: block;
  }

  /* Summary */
  .research-summary-text {
    font-size: 15px;
    line-height: 1.75;
    color: var(--text-primary);
    padding-left: 54px;
  }

  /* Lists */
  .research-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-left: 54px;
  }

  .research-list-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-primary);
  }

  .research-bullet {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--accent);
    flex-shrink: 0;
    margin-top: 7px;
    box-shadow: 0 0 0 3px var(--accent-bg);
  }

  .research-bullet-question {
    border-radius: 2px;
    background: #F59E0B;
    box-shadow: 0 0 0 3px rgba(245,158,11,0.12);
  }

  .research-list-text {
    flex: 1;
  }

  /* Questions list */
  .research-list-questions .research-list-item {
    color: var(--text-secondary);
    font-style: italic;
  }

  /* Sources */
  .research-source-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-left: 54px;
  }

  .research-source-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.15s ease;
  }
  .research-source-link:hover {
    border-color: var(--accent);
    background: var(--accent-bg);
    transform: translateX(4px);
  }

  .research-source-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--accent);
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .research-source-arrow {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: var(--surface);
    color: var(--text-muted);
    flex-shrink: 0;
    transition: all 0.15s;
  }
  .research-source-link:hover .research-source-arrow {
    background: var(--accent);
    color: white;
  }
  .research-source-arrow svg {
    width: 14px;
    height: 14px;
  }

  /* ── Mobile Responsive ── */
  @media (max-width: 768px) {
    .research-page {
      padding: 0 14px 80px;
    }
    
    .research-search {
      padding: 20px;
      border-radius: 16px;
    }
    
    .research-search-header {
      gap: 12px;
      margin-bottom: 20px;
    }
    
    .research-icon {
      width: 44px;
      height: 44px;
      border-radius: 14px;
    }
    .research-icon svg {
      width: 22px;
      height: 22px;
    }
    
    .research-search-title h2 {
      font-size: 18px;
    }
    
    .research-form {
      flex-direction: column;
      align-items: stretch;
      gap: 16px;
    }
    
    .research-field {
      flex: 1 1 100% !important;
    }
    
    .research-submit {
      width: 100%;
      justify-content: center;
      padding: 14px;
    }
    
    .research-card {
      padding: 20px;
      border-radius: 16px;
    }
    
    .research-card-header {
      margin-bottom: 16px;
    }
    
    .research-summary-text,
    .research-list,
    .research-source-list {
      padding-left: 0;
    }
    
    .research-list-item {
      gap: 10px;
    }
    
    .research-source-link {
      padding: 10px 14px;
    }
  }

  @media (max-width: 480px) {
    .research-page {
      padding: 0 12px 70px;
    }
    
    .research-search {
      padding: 16px;
    }
    
    .research-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
    }
    
    .research-search-title h2 {
      font-size: 16px;
    }
    
    .research-card {
      padding: 16px;
    }
    
    .research-card-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
    }
    
    .research-card-title {
      font-size: 15px;
    }
    
    .research-summary-text {
      font-size: 14px;
    }
    
    .research-list-item {
      font-size: 13px;
    }
  }
</style>
@endsection