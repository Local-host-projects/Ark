<?php

namespace App\Jobs;

use App\Models\Space;
use Illuminate\Bus\Queueable;
use App\Services\SpaceAudioService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateSpaceAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;

    public function __construct(public readonly Space $space) {}

    public function handle(SpaceAudioService $audioService): void
    {
        $audioService->generate($this->space);
    }

    public function failed(\Throwable $e): void
    {
        $this->space->update(['status' => 'failed']);
        Log::error('GenerateSpaceAudioJob failed', [
            'space_id' => $this->space->id,
            'error'    => $e->getMessage(),
        ]);
    }
}
