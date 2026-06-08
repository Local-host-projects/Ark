<?php

namespace App\Jobs;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Services\PostGeneratorService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateEventPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries   = 2;

    public function __construct(
        public readonly Story $story,
        public readonly array $event, // the timeline event array
    ) {}

    public function handle(PostGeneratorService $generator): void
    {
        $story = $this->story->fresh();

        // Only generate if story is still in a valid state
        if (!in_array($story->status, ['ready', 'running'])) {
            Log::warning('Story not in valid state for post generation', [
                'story_id' => $story->id,
                'status'   => $story->status,
            ]);
            return;
        }

        if ($story->status === 'ready') {
            $story->update(['status' => 'running']);
        }

        Log::info('Generating posts for event', [
            'story_id' => $story->id,
            'event'    => $this->event['title'],
            'sequence' => $this->event['sequence'],
        ]);

        $generator->generateForEvent($story, $this->event);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateEventPostsJob failed', [
            'story_id' => $this->story->id,
            'event'    => $this->event['title'] ?? 'unknown',
            'error'    => $e->getMessage(),
        ]);
    }
}
