<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Space;
use App\Models\Story;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SpaceAudioService
{
    private string $claudeUrl   = 'https://api.anthropic.com/v1/messages';
    private string $claudeModel = 'claude-sonnet-4-20250514';
    private string $aethexUrl   = 'https://api.aethexai.com/api/v1';

    public function __construct(private readonly VoiceAssignmentService $voiceAssigner) {}

    /**
     * Generate prerecorded conversation audio for a Space.
     * Each agent line is synthesized with their individually assigned voice.
     */
    public function generate(Space $space): void
    {
        $space->update(['status' => 'generating']);

        try {
            $story  = $space->story;
            $agents = $space->agents()->keyBy('name');

            // Ensure all agents have voices assigned
            foreach ($agents as $agent) {
                if (empty($agent->meta['aethex_voice_id'])) {
                    $this->voiceAssigner->assignVoice($agent);
                    $agent->refresh();
                }
            }

            // Generate conversation script
            $script = $this->generateScript($space, $agents);

            // Submit as batch — each line tagged with its agent's voice_id
            // AethexAI batch uses one voice_id for the whole batch,
            // so we group lines by voice and submit multiple batches
            $audioFiles = $this->synthesizeByVoice($script, $agents, $space->id);

            // Store playlist manifest in order
            $manifestPath = "spaces/{$space->id}/playlist.json";
            Storage::disk('public')->put($manifestPath, json_encode($audioFiles));

            $space->update([
                'audio_url'        => Storage::disk('public')->url($manifestPath),
                'duration_seconds' => $this->estimateDuration($script),
                'status'           => 'ready',
            ]);

        } catch (\Throwable $e) {
            Log::error('Space audio generation failed', [
                'space_id' => $space->id,
                'error'    => $e->getMessage(),
            ]);
            $space->update(['status' => 'failed']);
            throw $e;
        }
    }

    // ─── Script generation ────────────────────────────────────────────────────

    private function generateScript(Space $space, $agents): array
    {
        $agentList = $agents->map(fn($a) =>
            "{$a->name} ({$a->role}, {$a->affiliation})"
        )->implode(', ');

        $agentContext = $agents->map(fn($a) =>
            "{$a->name}: {$a->background}"
        )->implode("\n");

        $prompt = <<<PROMPT
Generate a realistic spoken conversation between these historical figures on {$space->historical_date}:

PARTICIPANTS: {$agentList}

TOPIC: {$space->title}
CONTEXT: {$space->description}

BACKGROUND:
{$agentContext}

Generate 8 to 12 lines of natural spoken dialogue.
Each figure speaks 1 to 3 sentences per turn.
All content must be historically accurate for {$space->historical_date}.
Figures only know what was publicly known on that exact date.

Return JSON only:
{
  "lines": [
    { "agent_name": "Exact name matching participant list", "text": "What they say" }
  ]
}
PROMPT;

                $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url', 'https://example.com'),
            'X-Title'       => 'Ark Historical Simulation',
        ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model'       => 'deepseek/deepseek-chat:free',
            'messages'    => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens'  => 2000,
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Script generation failed: ' . $response->body());
        }

        $text = preg_replace('/```json|```/', '', $response->json('choices.0.message.content'));
        $parsed = json_decode(trim($text), true);

        return $parsed['lines'] ?? throw new \RuntimeException('Invalid script format');
    }

    // ─── Voice synthesis ──────────────────────────────────────────────────────

    /**
     * Group script lines by voice_id and submit separate TTS batches per voice.
     * Merge results back into the original line order.
     * Returns array of [{ index, agent_name, url, duration_seconds }] sorted by index.
     */
    private function synthesizeByVoice(array $script, $agents, int $spaceId): array
    {
        // Group lines by voice_id
        $groups = [];
        foreach ($script as $index => $line) {
            $agent   = $agents[$line['agent_name']] ?? null;
            $voiceId = $agent?->meta['aethex_voice_id'] ?? 'default';

            $groups[$voiceId][] = [
                'original_index' => $index,
                'text'           => $line['text'],
                'agent_name'     => $line['agent_name'],
            ];
        }

        $allResults = [];

        foreach ($groups as $voiceId => $lines) {
            $items = array_map(fn($l) => [
                'text'     => substr($l['text'], 0, 2900),
                'language' => 'english',
            ], $lines);

            try {
                $batchId = $this->submitBatch($items, $voiceId);
                $results = $this->pollBatch($batchId);

                foreach ($results as $result) {
                    if ($result['status'] !== 'completed' || empty($result['audio_url'])) continue;

                    $originalIndex = $lines[$result['index']]['original_index'];
                    $agentName     = $lines[$result['index']]['agent_name'];

                    // Download and store the WAV
                    $audioContent = Http::timeout(30)->get($result['audio_url'])->body();
                    $filename     = "spaces/{$spaceId}/line_{$originalIndex}.wav";
                    Storage::disk('public')->put($filename, $audioContent);

                    $allResults[$originalIndex] = [
                        'index'            => $originalIndex,
                        'agent_name'       => $agentName,
                        'url'              => Storage::disk('public')->url($filename),
                        'duration_seconds' => $result['duration_seconds'] ?? null,
                        'voice_id'         => $voiceId,
                    ];
                }

            } catch (\Throwable $e) {
                Log::warning('Voice batch failed', [
                    'voice_id' => $voiceId,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        // Sort by original index so playlist plays in order
        ksort($allResults);
        return array_values($allResults);
    }

    private function submitBatch(array $items, string $voiceId): string
    {
        $response = Http::withHeaders([
            'X-API-Key'    => config('services.aethex.api_key'),
            'Content-Type' => 'application/json',
        ])->post("{$this->aethexUrl}/tts/batch", [
            'items'    => $items,
            'voice_id' => $voiceId,
            'language' => 'english',
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('TTS batch submit failed: ' . $response->body());
        }

        return $response->json('batch_id');
    }

    private function pollBatch(string $batchId, int $maxAttempts = 30): array
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(5);

            $response = Http::withHeaders([
                'X-API-Key' => config('services.aethex.api_key'),
            ])->get("{$this->aethexUrl}/tts/batch/{$batchId}");

            if (!$response->successful()) continue;

            $data = $response->json();

            if ($data['status'] === 'completed') return $data['results'];
            if ($data['status'] === 'failed') throw new \RuntimeException("Batch {$batchId} failed");
        }

        throw new \RuntimeException("Batch {$batchId} timed out");
    }

    private function estimateDuration(array $script): int
    {
        $words = collect($script)->sum(fn($l) => str_word_count($l['text']));
        return (int) ceil(($words / 150) * 60);
    }
}
