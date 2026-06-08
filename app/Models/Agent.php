<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    protected $fillable = [
        'story_id',
        'name',
        'type',
        'role',
        'affiliation',
        'location',
        'latitude',
        'longitude',
        'system_prompt',
        'background',
        'goals',
        'concerns',
        'tools',
        'avatar_url',
        'memory',
        'meta',
    ];

    protected $casts = [
        'goals'    => 'array',
        'concerns' => 'array',
        'tools'    => 'array',
        'memory'   => 'array',
        'meta'     => 'array',
        'latitude' => 'decimal:7',
        'longitude'=> 'decimal:7',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('sequence');
    }

    public function canUse(string $tool): bool
    {
        return in_array($tool, $this->tools ?? ['text']);
    }

    public function appendMemory(string $entry): void
    {
        $memory = $this->memory ?? [];
        $memory[] = ['at' => now()->toISOString(), 'entry' => $entry];

        if (count($memory) > 50) {
            $memory = array_slice($memory, -50);
        }

        $this->update(['memory' => $memory]);
    }

    public function memoryAsString(): string
    {
        return collect($this->memory ?? [])
            ->map(fn($m) => "[{$m['at']}] {$m['entry']}")
            ->implode("\n");
    }
}