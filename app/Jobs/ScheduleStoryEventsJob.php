<?php

namespace App\Jobs;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScheduleStoryEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries   = 2;

    private const MIN_INTERVAL = 30;  // minutes
    private const MAX_INTERVAL = 120; // minutes

    public function __construct(public readonly Story $story) {}

    /**
     * Assigns real-world scheduled_at times to each timeline event.
     * Event 1 fires immediately (now).
     * Subsequent events fire at random 30-120 minute intervals.
     *
     * The actual firing is handled by EventProcessorController
     * which is called by an external cron every 1-5 minutes.
     */
    public function handle(): void
    {
        $story    = $this->story->fresh();
        $timeline = $story->timeline ?? [];

        if (empty($timeline)) {
            Log::warning('No timeline to schedule', ['story_id' => $story->id]);
            return;
        }

        usort($timeline, fn($a, $b) => $a['sequence'] <=> $b['sequence']);

        $cursor = Carbon::now();

        foreach ($timeline as $index => $event) {
            if ($index === 0) {
                $scheduledAt = $cursor->copy();
            } else {
                $intervalMinutes = rand(self::MIN_INTERVAL, self::MAX_INTERVAL);
                $cursor = $cursor->copy()->addMinutes($intervalMinutes);
                $scheduledAt = $cursor->copy();
            }

            $timeline[$index]['scheduled_at'] = $scheduledAt->toISOString();

            Log::info('Event time assigned', [
                'story_id'    => $story->id,
                'sequence'    => $event['sequence'],
                'title'       => $event['title'],
                'scheduled_at'=> $scheduledAt->toISOString(),
            ]);
        }

        $story->update(['timeline' => $timeline, 'status' => 'ready']);

        Log::info('Story scheduled via cron model', [
            'story_id'     => $story->id,
            'total_events' => count($timeline),
            'first_fires'  => $timeline[0]['scheduled_at'] ?? 'now',
            'last_fires'   => end($timeline)['scheduled_at'] ?? 'unknown',
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ScheduleStoryEventsJob failed', [
            'story_id' => $this->story->id,
            'error'    => $e->getMessage(),
        ]);
    }
}