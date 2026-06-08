<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'timeline',
        'sources',
        'status',
        'period_start',
        'period_end',
        'current_sequence',
    ];

    protected $casts = [
        'timeline' => 'array',
        'sources'  => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('sequence');
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    public function isOwned(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    // ─── Timeline helpers ─────────────────────────────────────────────────────

    /**
     * Get a single event from the timeline JSON by sequence number.
     */
    public function getEvent(int $sequence): ?array
    {
        return collect($this->timeline ?? [])
            ->firstWhere('sequence', $sequence);
    }

    /**
     * Get all events up to and including the current sequence.
     * These are the events that have fired so far.
     */
    public function firedEvents(): array
    {
        return collect($this->timeline ?? [])
            ->where('sequence', '<=', $this->current_sequence)
            ->values()
            ->toArray();
    }

    /**
     * Get the next unfired event.
     */
    public function nextEvent(): ?array
    {
        return collect($this->timeline ?? [])
            ->where('sequence', '>', $this->current_sequence)
            ->sortBy('sequence')
            ->first();
    }

    /**
     * Total number of events in the timeline.
     */
    public function totalEvents(): int
    {
        return count($this->timeline ?? []);
    }

    /**
     * Progress through the timeline as a percentage.
     */
    public function progressPercent(): float
    {
        $total = $this->totalEvents();
        if ($total === 0) return 0;
        return round(($this->current_sequence / $total) * 100, 1);
    }

    /**
     * Advance the story's current sequence marker.
     */
    public function advanceTo(int $sequence): void
    {
        if ($sequence > $this->current_sequence) {
            $this->update(['current_sequence' => $sequence]);
        }
    }
}
