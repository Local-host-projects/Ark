<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Space extends Model
{
    protected $fillable = [
        'story_id',
        'title',
        'description',
        'unlocks_at_sequence',
        'historical_date',
        'agent_ids',
        'audio_url',
        'duration_seconds',
        'status',
    ];

    protected $casts = [
        'agent_ids' => 'array',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function isUnlockedFor(Story $story): bool
    {
        return $story->current_sequence >= $this->unlocks_at_sequence;
    }

    public function agents(): \Illuminate\Database\Eloquent\Collection
    {
        return Agent::whereIn('id', $this->agent_ids ?? [])->get();
    }
}
