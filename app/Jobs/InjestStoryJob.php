<?php

namespace App\Jobs;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use App\Services\IngestionService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InjestStoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;

    public function __construct(public readonly Story $story) {}

    public function handle(IngestionService $ingestion): void
    {
        // Run ingestion — builds agents + timeline
        $ingestion->ingest($this->story);

        // After ingestion, schedule all timeline events
        ScheduleStoryEventsJob::dispatch($this->story);
    }

    public function failed(\Throwable $e): void
    {
        $this->story->update(['status' => 'failed']);
    }
}
