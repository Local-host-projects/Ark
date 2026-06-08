<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Services\IngestionService;
use Illuminate\Support\Facades\Log;
use App\Services\PostGeneratorService;

class EventProcessorController extends Controller
{
    public function __construct(
        private readonly PostGeneratorService $generator
    ) {}

    /**
     * GET|POST /internal/process-events
     *
     * Called by an external cron service (cron-job.org) every 1 minute.
     *
     * Phase 1: Ingests pending stories (Claude → agents + timeline).
     * Phase 2: Fires the first event immediately so posts appear right away.
     * Phase 3: Fires any other due timeline events for ready/running stories.
     *
     * Protected by a simple secret token in the Authorization header
     * or as a query param: ?token=YOUR_CRON_SECRET
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify cron secret
        $secret = config('services.cron_secret');
        $provided = $request->bearerToken()
            ?? $request->query('token')
            ?? $request->input('token');

        if ($secret && $provided !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $now       = Carbon::now();
        $processed = [];
        $errors    = [];

        // ─── PHASE 1: Ingest pending stories ───────────────────────────────────
        // Pick up stories that were just created and run the heavy Claude work.
        // Also recover stories stuck in 'processing' for > 15 min (previous cron died).
        $pending = Story::where('status', 'pending')
            ->orWhere(function ($q) {
                $q->where('status', 'processing')
                  ->where('updated_at', '<', now()->subMinutes(15));
            })
            ->get();

        foreach ($pending as $story) {
            // Mark as processing so overlapping cron jobs skip it
            $story->update(['status' => 'processing']);

            try {
                Log::info('Cron ingesting story', ['story_id' => $story->id]);

                // 1. Build agents + timeline via Claude
                app(IngestionService::class)->ingest($story);

                // 2. Assign scheduled_at times to events
                (new \App\Jobs\ScheduleStoryEventsJob($story))->handle();

                // 3. Fire the first event immediately so the user sees posts
                $story = $story->fresh();
                $firstEvent = $story->getEvent(1);

                if ($firstEvent) {
                    Log::info('Cron firing first event', [
                        'story_id' => $story->id,
                        'event'    => $firstEvent['title'],
                    ]);

                    $this->generator->generateForEvent($story, $firstEvent);

                    $processed[] = [
                        'story_id'  => $story->id,
                        'story'     => $story->title,
                        'event'     => $firstEvent['title'],
                        'sequence'  => 1,
                        'phase'     => 'ingest_and_fire',
                    ];
                }

            } catch (\Throwable $e) {
                $story->update(['status' => 'failed']);
                $errors[] = [
                    'story_id' => $story->id,
                    'phase'    => 'ingestion',
                    'error'    => $e->getMessage(),
                ];
                Log::error('Cron ingestion failed', [
                    'story_id' => $story->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        // ─── PHASE 2: Fire due events for ready/running stories ────────────────
        $stories = Story::whereIn('status', ['ready', 'running'])->get();

        foreach ($stories as $story) {
            $timeline = $story->timeline ?? [];

            if (empty($timeline)) continue;

            usort($timeline, fn($a, $b) => $a['sequence'] <=> $b['sequence']);

            foreach ($timeline as $event) {
                $seq         = $event['sequence'];
                $scheduledAt = isset($event['scheduled_at'])
                    ? Carbon::parse($event['scheduled_at'])
                    : null;

                if (!$scheduledAt) continue;
                if ($scheduledAt->isAfter($now)) continue;
                if ($seq <= $story->current_sequence) continue;

                // Enforce sequential order
                if ($seq > $story->current_sequence + 1) {
                    $prevEvent = collect($timeline)->firstWhere('sequence', $seq - 1);
                    if ($prevEvent && isset($prevEvent['scheduled_at'])) {
                        $prevTime = Carbon::parse($prevEvent['scheduled_at']);
                        if ($prevTime->isAfter($now)) continue;
                    }
                    if ($story->current_sequence < $seq - 1) continue;
                }

                try {
                    Log::info('Cron firing event', [
                        'story_id' => $story->id,
                        'event'    => $event['title'],
                        'sequence' => $seq,
                    ]);

                    $this->generator->generateForEvent($story->fresh(), $event);

                    $processed[] = [
                        'story_id'  => $story->id,
                        'story'     => $story->title,
                        'event'     => $event['title'],
                        'sequence'  => $seq,
                        'phase'     => 'fire_event',
                    ];

                } catch (\Throwable $e) {
                    Log::error('Cron event fire failed', [
                        'story_id' => $story->id,
                        'event'    => $event['title'],
                        'error'    => $e->getMessage(),
                    ]);

                    $errors[] = [
                        'story_id' => $story->id,
                        'event'    => $event['title'],
                        'error'    => $e->getMessage(),
                    ];
                }
            }
        }

        return response()->json([
            'fired'     => count($processed),
            'errors'    => count($errors),
            'processed' => $processed,
            'checked_at'=> $now->toISOString(),
        ]);
    }

    /**
     * GET /internal/status
     * Quick health check — shows pending events across all stories.
     */
    public function status(Request $request): JsonResponse
    {
        $secret   = config('services.cron_secret');
        $provided = $request->bearerToken() ?? $request->query('token');

        if ($secret && $provided !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $now     = Carbon::now();
        $stories = Story::whereIn('status', ['ready', 'running', 'pending', 'processing'])->get();

        $report = $stories->map(function ($story) use ($now) {
            $timeline = $story->timeline ?? [];
            usort($timeline, fn($a, $b) => $a['sequence'] <=> $b['sequence']);

            $events = collect($timeline)->map(function ($event) use ($story, $now) {
                $seq         = $event['sequence'];
                $scheduledAt = isset($event['scheduled_at']) ? Carbon::parse($event['scheduled_at']) : null;
                $fired       = $seq <= $story->current_sequence;
                $due         = $scheduledAt && $scheduledAt->isPast();
                $minutesUntil= $scheduledAt ? round($scheduledAt->diffInMinutes($now, false)) : null;

                return [
                    'sequence'     => $seq,
                    'title'        => $event['title'],
                    'scheduled_at' => $scheduledAt?->toISOString(),
                    'minutes_until'=> $minutesUntil,
                    'status'       => $fired ? 'fired' : ($due ? 'due_now' : 'pending'),
                ];
            });

            return [
                'story_id'         => $story->id,
                'title'            => $story->title,
                'status'           => $story->status,
                'current_sequence' => $story->current_sequence,
                'total_events'     => count($timeline),
                'events'           => $events,
            ];
        });

        return response()->json([
            'server_time' => $now->toISOString(),
            'stories'     => $report,
        ]);
    }
}