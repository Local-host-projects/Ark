<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Story;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IngestionService
{
    private string $claudeUrl   = 'https://api.anthropic.com/v1/messages';
    private string $claudeModel = 'claude-sonnet-4-6';

    public function __construct(
        private readonly VoiceAssignmentService $voiceAssigner
    ) {}

    public function ingest(Story $story): void
    {
        $story->update(['status' => 'processing']);

        try {
            $rawContent = $this->gatherSources($story);
            $extracted  = $this->extract($rawContent, $story);

            foreach ($extracted['agents'] as $agentData) {
                Agent::create([
                    'story_id'      => $story->id,
                    'name'          => $agentData['name'],
                    'type'          => $agentData['type'] ?? 'person',
                    'role'          => $agentData['role'] ?? null,
                    'affiliation'   => $agentData['affiliation'] ?? null,
                    'location'      => $agentData['location'] ?? null,
                    'latitude'      => $agentData['latitude'] ?? null,
                    'longitude'     => $agentData['longitude'] ?? null,
                    'system_prompt' => $this->buildSystemPrompt($agentData),
                    'background'    => $agentData['background'] ?? null,
                    'goals'         => $agentData['goals'] ?? [],
                    'concerns'      => $agentData['concerns'] ?? [],
                    'tools'         => $agentData['tools'] ?? ['text'],
                    'memory'        => [],
                ]);
            }

            $this->voiceAssigner->assignToStoryAgents($story);

            $agentNameToId = Agent::where('story_id', $story->id)
                ->pluck('id', 'name')
                ->toArray();

            $timeline = collect($extracted['timeline'])->map(function ($event) use ($agentNameToId) {
                $event['involved_agent_ids'] = collect($event['involved_agent_names'] ?? [])
                    ->map(fn($name) => $agentNameToId[$name] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();
                unset($event['involved_agent_names']);
                return $event;
            })->toArray();

            $story->update([
                'status'       => 'ready',
                'timeline'     => $timeline,
                'period_start' => $extracted['period_start'] ?? null,
                'period_end'   => $extracted['period_end'] ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Ingestion failed', ['story_id' => $story->id, 'error' => $e->getMessage()]);
            $story->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function gatherSources(Story $story): string
    {
        $sources = $story->sources ?? [];
        $parts   = [];

        foreach ($sources as $source) {
            $parts[] = match($source['type']) {
                'url'   => $this->fetchUrl($source['content']),
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

    private function extract(string $rawContent, Story $story): array
    {
        $prompt = <<<PROMPT
You are a dramatic story architect and historian.

Analyze this source material and structure it as a dramatically compelling story told through social media posts.

The timeline MUST follow a five-beat dramatic structure borrowed from Shakespeare and Freytag:
- Beat 1 "exposition"     (1-2 events): World, agents, and stakes established
- Beat 2 "inciting"       (1 event):    The moment that makes the status quo impossible — a shock
- Beat 3 "rising_action"  (3-4 events): Escalating stakes, alliances forming and breaking, foreshadowing
- Beat 4 "crisis"         (1-2 events): Point of no return — the road back is blocked
- Beat 5 "climax"         (1-2 events): Everything converges, consequences land

Total events: 8 to 10 maximum. Every event must earn its place.
Each event should feel like a scene in a play — tense, purposeful, moving the story forward.

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
      "cliffhanger": "One sentence — what question does this leave unanswered that pulls you to the next event",
      "foreshadowing": "One subtle detail planted here that pays off later — or null",
      "date": "YYYY-MM-DD",
      "location": { "name": "Place name", "latitude": null, "longitude": null },
      "type": "sequential",
      "parallel_group": null,
      "involved_agent_names": ["Name1", "Name2"]
    }
  ]
}

Rules:
- Keep agents to the most dramatically essential characters — max 8
- Every event has a cliffhanger — something unresolved that makes the next event necessary
- Beat 4 (crisis) must be the heaviest emotional event — the point where everything changes
- Parallel events (same parallel_group integer) fire simultaneously
- involved_agent_names must match agent names exactly
- Return ONLY the JSON

SOURCE MATERIAL:
{$rawContent}
PROMPT;

        $response = Http::withHeaders([
            'x-api-key'         => config('services.claude.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post($this->claudeUrl, [
            'model'      => $this->claudeModel,
            'max_tokens' => 6000,
            'system'     => 'You are a historical simulation architect and dramatic storyteller. Return only valid JSON.',
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Claude ingestion failed: ' . $response->body());
        }

        $text = preg_replace('/```json|```/', '', $response->json('content.0.text'));
        $parsed = json_decode(trim($text), true);

        if (!$parsed) {
            throw new \RuntimeException('Failed to parse ingestion response');
        }

        return $parsed;
    }

    private function getScheduleHint(array $sources): string
    {
        foreach ($sources as $source) {
            if (!empty($source['schedule_hint'])) {
                return $source['schedule_hint'];
            }
        }
        return 'Space events starting from now, one event every 2 hours.';
    }

    private function buildSystemPrompt(array $data): string
    {
        $goals    = implode(', ', array_slice($data['goals'] ?? [], 0, 3));
        $concerns = implode(', ', array_slice($data['concerns'] ?? [], 0, 3));
        $traits   = implode(', ', array_slice($data['personality_traits'] ?? [], 0, 3));

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