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

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isOwned(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function getEvent(int $sequence): ?array
    {
        return collect($this->timeline ?? [])
            ->firstWhere('sequence', $sequence);
    }

    public function firedEvents(): array
    {
        return collect($this->timeline ?? [])
            ->where('sequence', '<=', $this->current_sequence)
            ->values()
            ->toArray();
    }

    public function nextEvent(): ?array
    {
        return collect($this->timeline ?? [])
            ->where('sequence', '>', $this->current_sequence)
            ->sortBy('sequence')
            ->first();
    }

    public function totalEvents(): int
    {
        return count($this->timeline ?? []);
    }

    public function progressPercent(): float
    {
        $total = $this->totalEvents();
        if ($total === 0) return 0;
        return round(($this->current_sequence / $total) * 100, 1);
    }

    public function advanceTo(int $sequence): void
    {
        if ($sequence > $this->current_sequence) {
            $this->update(['current_sequence' => $sequence]);
        }
    }
}