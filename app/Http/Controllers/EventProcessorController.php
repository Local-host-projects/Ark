<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Agent;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\PostGeneratorService;
use App\Services\VoiceAssignmentService;

class EventProcessorController extends Controller
{
    public function __construct(
        private readonly PostGeneratorService $generator
    ) {}

    /**
     * GET|POST /internal/process-events
     *
     * Called by cron-job.org every 1 minute.
     * Picks up the oldest pending task and executes exactly ONE step.
     * Each step is small enough to finish within 25 seconds.
     */
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.cron_secret');
        $provided = $request->bearerToken()
            ?? $request->query('token')
            ?? $request->input('token');

        if ($secret && $provided !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Find one pending task (oldest first)
        $task = Task::pending()->first();

        if (!$task) {
            // No tasks — just fire any due events for running stories
            $fired = $this->fireDueEvents();
            return response()->json([
                'status'     => 'tick_complete',
                'task'       => null,
                'fired'      => $fired,
                'checked_at' => now()->toISOString(),
            ]);
        }

        $task->markRunning();
        $story = $task->story;

        try {
            match ($task->type) {
                'ingest_timeline'  => $this->handleIngestTimeline($task, $story),
                'create_agent'     => $this->handleCreateAgent($task, $story),
                'schedule_events'  => $this->handleScheduleEvents($task, $story),
                'fire_event'       => $this->handleFireEvent($task, $story),
                default            => throw new \RuntimeException("Unknown task type: {$task->type}"),
            };

            $task->markDone();

            return response()->json([
                'status'   => 'task_complete',
                'task_id'  => $task->id,
                'type'     => $task->type,
                'story_id' => $story->id,
            ]);

        } catch (\Throwable $e) {
            $task->markFailed($e->getMessage());
            Log::error('Task failed', [
                'task_id'  => $task->id,
                'type'     => $task->type,
                'story_id' => $story->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'status'   => 'task_failed',
                'task_id'  => $task->id,
                'type'     => $task->type,
                'error'    => $e->getMessage(),
            ], 500);
        }
    }

    // ─── TASK HANDLERS ───────────────────────────────────────────────────────

    private function handleIngestTimeline(Task $task, Story $story): void
    {
        $story->update(['status' => 'ingesting']);

        $rawContent = $this->gatherSources($story);
        $extracted = $this->callClaudeForIngestion($rawContent);

        if (!$extracted) {
            throw new \RuntimeException('Claude ingestion failed or timed out');
        }

        // Save timeline + period on story
        $story->update([
            'status'       => 'ingesting',
            'timeline'     => $extracted['timeline'] ?? [],
            'period_start' => $extracted['period_start'] ?? null,
            'period_end'   => $extracted['period_end'] ?? null,
        ]);

        // Create tasks for each agent (capped at 7)
        $agents = array_slice($extracted['agents'] ?? [], 0, 7);
        foreach ($agents as $index => $agentData) {
            Task::create([
                'story_id' => $story->id,
                'type'     => 'create_agent',
                'payload'  => ['agent_data' => $agentData, 'index' => $index],
            ]);
        }

        // After all agents, schedule events
        Task::create([
            'story_id' => $story->id,
            'type'     => 'schedule_events',
        ]);

        Log::info('Timeline ingested, agents queued', [
            'story_id'     => $story->id,
            'agent_count'  => count($agents),
        ]);
    }

    private function handleCreateAgent(Task $task, Story $story): void
    {
        $data = $task->payload['agent_data'] ?? null;
        if (!$data) throw new \RuntimeException('Missing agent data in task payload');

        Agent::create([
            'story_id'      => $story->id,
            'name'          => $data['name'],
            'type'          => $data['type'] ?? 'person',
            'role'          => $data['role'] ?? null,
            'affiliation'   => $data['affiliation'] ?? null,
            'location'      => $data['location'] ?? null,
            'latitude'      => $data['latitude'] ?? null,
            'longitude'     => $data['longitude'] ?? null,
            'system_prompt' => $this->buildSystemPrompt($data),
            'background'    => $data['background'] ?? null,
            'goals'         => $data['goals'] ?? [],
            'concerns'      => $data['concerns'] ?? [],
            'tools'         => $data['tools'] ?? ['text'],
            'memory'        => [],
        ]);

        Log::info('Agent created', ['story_id' => $story->id, 'agent' => $data['name']]);
    }

    private function handleScheduleEvents(Task $task, Story $story): void
    {
        $story->update(['status' => 'scheduling']);

        // Assign voices
        app(VoiceAssignmentService::class)->assignToStoryAgents($story);

        // Map agent names to IDs
        $agentNameToId = Agent::where('story_id', $story->id)
            ->pluck('id', 'name')
            ->toArray();

        // Resolve involved_agent_ids in timeline
        $timeline = collect($story->timeline ?? [])->map(function ($event) use ($agentNameToId) {
            $event['involved_agent_ids'] = collect($event['involved_agent_names'] ?? [])
                ->map(fn($name) => $agentNameToId[$name] ?? null)
                ->filter()
                ->values()
                ->toArray();
            unset($event['involved_agent_names']);
            return $event;
        })->toArray();

        $story->update(['timeline' => $timeline]);

        // Schedule times
        (new \App\Jobs\ScheduleStoryEventsJob($story))->handle();

        // Create tasks for each event
        $timeline = $story->fresh()->timeline ?? [];
        usort($timeline, fn($a, $b) => $a['sequence'] <=> $b['sequence']);

        foreach ($timeline as $event) {
            Task::create([
                'story_id' => $story->id,
                'type'     => 'fire_event',
                'payload'  => ['sequence' => $event['sequence']],
            ]);
        }

        Log::info('Events scheduled', [
            'story_id'     => $story->id,
            'event_count'  => count($timeline),
        ]);
    }

    private function handleFireEvent(Task $task, Story $story): void
    {
        $sequence = $task->payload['sequence'] ?? null;
        if (!$sequence) throw new \RuntimeException('Missing sequence in task payload');

        $event = $story->getEvent((int) $sequence);
        if (!$event) throw new \RuntimeException("Event {$sequence} not found");

        // Check if event is due
        $scheduledAt = isset($event['scheduled_at']) ? Carbon::parse($event['scheduled_at']) : null;
        if ($scheduledAt && $scheduledAt->isAfter(now())) {
            // Not due yet — re-queue this task for later
            Task::create([
                'story_id' => $story->id,
                'type'     => 'fire_event',
                'payload'  => ['sequence' => $sequence],
            ]);
            Log::info('Event not due yet, re-queued', [
                'story_id' => $story->id,
                'sequence' => $sequence,
                'due_in'   => $scheduledAt->diffForHumans(),
            ]);
            return;
        }

        // Check sequential order
        if ($sequence > $story->current_sequence + 1) {
            // Previous event not fired yet — re-queue
            Task::create([
                'story_id' => $story->id,
                'type'     => 'fire_event',
                'payload'  => ['sequence' => $sequence],
            ]);
            Log::info('Event out of sequence, re-queued', [
                'story_id' => $story->id,
                'sequence' => $sequence,
            ]);
            return;
        }

        $this->generator->generateForEvent($story->fresh(), $event);

        Log::info('Event fired', [
            'story_id' => $story->id,
            'sequence' => $sequence,
            'event'    => $event['title'],
        ]);
    }

    // ─── FIRE DUE EVENTS (for stories already running, no tasks needed) ──────

    private function fireDueEvents(): int
    {
        $now = Carbon::now();
        $stories = Story::whereIn('status', ['ready', 'running'])->get();
        $fired = 0;

        foreach ($stories as $story) {
            $timeline = $story->timeline ?? [];
            if (empty($timeline)) continue;

            usort($timeline, fn($a, $b) => $a['sequence'] <=> $b['sequence']);

            foreach ($timeline as $event) {
                $seq = $event['sequence'];
                $scheduledAt = isset($event['scheduled_at']) ? Carbon::parse($event['scheduled_at']) : null;

                if (!$scheduledAt || $scheduledAt->isAfter($now)) continue;
                if ($seq <= $story->current_sequence) continue;

                if ($seq > $story->current_sequence + 1) {
                    $prevEvent = collect($timeline)->firstWhere('sequence', $seq - 1);
                    if ($prevEvent && isset($prevEvent['scheduled_at'])) {
                        $prevTime = Carbon::parse($prevEvent['scheduled_at']);
                        if ($prevTime->isAfter($now)) continue;
                    }
                    if ($story->current_sequence < $seq - 1) continue;
                }

                try {
                    $this->generator->generateForEvent($story->fresh(), $event);
                    $fired++;
                } catch (\Throwable $e) {
                    Log::error('Cron event fire failed', [
                        'story_id' => $story->id,
                        'event'    => $event['title'],
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        return $fired;
    }

    // ─── STATUS ENDPOINT ─────────────────────────────────────────────────────

    public function status(Request $request): JsonResponse
    {
        $secret = config('services.cron_secret');
        $provided = $request->bearerToken() ?? $request->query('token');

        if ($secret && $provided !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $stories = Story::withCount(['agents', 'posts'])
            ->latest()
            ->get();

        $tasks = Task::whereIn('story_id', $stories->pluck('id'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('story_id');

        $report = $stories->map(function ($story) use ($tasks) {
            $storyTasks = $tasks[$story->id] ?? collect();
            $pending = $storyTasks->where('status', 'pending')->count();
            $done = $storyTasks->where('status', 'done')->count();
            $failed = $storyTasks->where('status', 'failed')->count();

            return [
                'story_id'      => $story->id,
                'title'         => $story->title,
                'status'        => $story->status,
                'agents_count'  => $story->agents_count,
                'posts_count'   => $story->posts_count,
                'current_seq'   => $story->current_sequence,
                'tasks_pending' => $pending,
                'tasks_done'    => $done,
                'tasks_failed'  => $failed,
                'next_task'     => $storyTasks->firstWhere('status', 'pending')?->type,
            ];
        });

        return response()->json([
            'server_time' => now()->toISOString(),
            'stories'     => $report,
        ]);
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    private function gatherSources(Story $story): string
    {
        $sources = $story->sources ?? [];
        $parts = [];

        foreach ($sources as $source) {
            $parts[] = match($source['type']) {
                'url' => $this->fetchUrl($source['content']),
                default => $source['content'] ?? '',
            };
        }

        return implode("\n\n---\n\n", array_filter($parts));
    }

    private function fetchUrl(string $url): string
    {
        try {
            $html = Http::timeout(15)->get($url)->body();
            $text = strip_tags($html);
            return trim(substr(preg_replace('/\s+/', ' ', $text), 0, 20000));
        } catch (\Throwable $e) {
            return "Could not fetch: {$url}";
        }
    }

    private function callClaudeForIngestion(string $rawContent): ?array
    {
        $prompt = <<<PROMPT
You are a dramatic story architect and historian.

Analyze this source material and structure it as a dramatically compelling story told through social media posts.

The timeline MUST follow a five-beat dramatic structure:
- Beat 1 "exposition"     (1-2 events): World, agents, and stakes established
- Beat 2 "inciting"       (1 event):    The shock that changes everything
- Beat 3 "rising_action"  (3-4 events): Escalating stakes, alliances breaking
- Beat 4 "crisis"         (1-2 events): Point of no return
- Beat 5 "climax"         (1-2 events): Everything converges

Total events: 8 to 10 maximum.
Agents: 5 to 7 maximum.

Return this exact JSON structure:
{
  "period_start": "YYYY-MM-DD",
  "period_end": "YYYY-MM-DD",
  "agents": [
    {
      "name": "Full name",
      "type": "person | organisation | media | government",
      "role": "Their title",
      "affiliation": "Party, country, institution",
      "location": "Primary base",
      "latitude": null,
      "longitude": null,
      "background": "2 sentence historical context",
      "goals": ["goal1", "goal2"],
      "concerns": ["concern1", "concern2"],
      "tools": ["text"],
      "communication_style": "How they write publicly",
      "personality_traits": ["trait1", "trait2"]
    }
  ],
  "timeline": [
    {
      "sequence": 1,
      "beat": "exposition",
      "title": "Short punchy event title",
      "description": "What happens and why it matters dramatically",
      "emotional_register": "calm | tense | urgent | shocked | fearful | triumphant | devastating",
      "cliffhanger": "One sentence question that pulls to next event",
      "foreshadowing": "One subtle detail that pays off later — or null",
      "date": "YYYY-MM-DD",
      "location": { "name": "Place name", "latitude": null, "longitude": null },
      "type": "sequential",
      "parallel_group": null,
      "involved_agent_names": ["Name1", "Name2"]
    }
  ]
}

Rules:
- Max 7 agents
- Every event has a cliffhanger
- involved_agent_names must match agent names exactly
- Return ONLY the JSON

SOURCE MATERIAL:
{$rawContent}
PROMPT;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.claude.api_key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(25)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-20250514',
                'max_tokens' => 4000,
                'system'     => 'You are a historical simulation architect and dramatic storyteller. Return only valid JSON.',
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            if (!$response->successful()) {
                Log::warning('Claude error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $text = preg_replace('/```json|```/', '', $response->json('content.0.text'));
            $parsed = json_decode(trim($text), true);

            if (!$parsed || !isset($parsed['agents']) || !isset($parsed['timeline'])) {
                Log::warning('Claude invalid JSON', ['text' => $text]);
                return null;
            }

            return $parsed;

        } catch (\Throwable $e) {
            Log::warning('Claude timeout', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function buildSystemPrompt(array $data): string
    {
        $goals = implode(', ', array_slice($data['goals'] ?? [], 0, 3));
        $concerns = implode(', ', array_slice($data['concerns'] ?? [], 0, 3));
        $traits = implode(', ', array_slice($data['personality_traits'] ?? [], 0, 3));
        $voice = $data['communication_style'] ?? 'Direct and authentic';

        return <<<PROMPT
You are {$data['name']}, {$data['role']} ({$data['affiliation']}).
Background: {$data['background']}
Goals: {$goals}
Concerns: {$concerns}
Voice: {$voice}. Traits: {$traits}.
Rule: Only know what was publicly known to you at the exact moment you post. Never reference the future. Stay in character always.
PROMPT;
    }
}