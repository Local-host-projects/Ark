<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'story_id',
        'agent_id',
        'timeline_event_sequence',
        'parent_post_id',
        'status',
        'content',
        'content_type',
        'media',
        'historical_date',
        'sequence',
        'location_name',
        'latitude',
        'longitude',
        'meta',
    ];

    protected $casts = [
        'media'    => 'array',
        'meta'     => 'array',
        'latitude' => 'decimal:7',
        'longitude'=> 'decimal:7',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_post_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_post_id')->orderBy('sequence');
    }

    public function isReply(): bool
    {
        return $this->status === 'reply';
    }

    public function isStandalone(): bool
    {
        return $this->status === 'standalone';
    }
}
