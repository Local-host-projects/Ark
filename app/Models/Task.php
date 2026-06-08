<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'story_id',
        'type',
        'payload',
        'status',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->orderBy('created_at');
    }

    public function markRunning(): void
    {
        $this->update(['status' => 'running']);
    }

    public function markDone(): void
    {
        $this->update(['status' => 'done']);
    }

    public function markFailed(string $error): void
    {
        $this->update(['status' => 'failed', 'error' => $error]);
    }
}