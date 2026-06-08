<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Post;
use App\Models\Story;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostGeneratorService
{
    private string $claudeUrl   = 'https://api.anthropic.com/v1/messages';
    private string $claudeModel = 'claude-sonnet-4-20250514';

    // Hard cap — total posts + replies per story
    private const POST_CAP = 50;

    // Compressed feed context — keeps input tokens low
    private const FEED_CONTEXT_LIMIT  = 8;
    private const FEED_CONTENT_TRUNCATE = 100;

    public function __construct(private readonly ImageService $imageService) {}

    /**
     * Generate posts for all agents involved in a timeline event.
     * Caps total story posts at POST_CAP.
     * Uses batch reply decision to reduce API calls.
     */
    public function generateForEvent(Story $story, array $event): void
    {
        $agentIds = $event['involved_agent_ids'] ?? [];
        if (empty($agentIds)) return;

        $agents         = Agent::whereIn('id', $agentIds)->get();
        $allStoryAgents = Agent::where('story_id', $story->id)->get();
        $globalSequence = Post::where('story_id', $story->id)->max('sequence') ?? 0;

        $recentPosts = $this->getCompressedFeed($story);

        foreach ($agents as $agent) {
            if ($this->atCap($story)) {
                Log::info('Post cap reached', ['story_id' => $story->id, 'event' => $event['title']]);
                break;
            }

            try {
                $posts = $this->generateStandalonePosts(
                    story: $story,
                    event: $event,
                    agent: $agent,
                    recentPosts: $recentPosts,
                    sequence: $globalSequence,
                );

                foreach ($posts as $post) {
                    $globalSequence = $post->sequence;
                    $recentPosts->push($post->load('agent:id,name,role'));

                    if (!$this->atCap($story)) {
                        $replies = $this->generateReplies(
                            story: $story,
                            parentPost: $post,
                            allAgents: $allStoryAgents,
                            sequence: $globalSequence,
                        );

                        foreach ($replies as $reply) {
                            $globalSequence = $reply->sequence;
                            $recentPosts->push($reply->load('agent:id,name,role'));
                        }
                    }
                }

                $agent->appendMemory("Posted about: {$event['title']} on {$event['date']}");

            } catch (\Throwable $e) {
                Log::warning('Post generation failed', [
                    'agent_id' => $agent->id,
                    'event'    => $event['title'],
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        $story->advanceTo($event['sequence']);
    }

    // ─── Cap check ────────────────────────────────────────────────────────────

    private function atCap(Story $story): bool
    {
        return Post::where('story_id', $story->id)->count() >= self::POST_CAP;
    }

    // ─── Compressed feed ──────────────────────────────────────────────────────

    /**
     * Fetch last N posts with content truncated.
     * Reduces input tokens on every generation call.
     */
    private function getCompressedFeed(Story $story)
    {
        return Post::where('story_id', $story->id)
            ->with('agent:id,name,role')
            ->orderByDesc('sequence')
            ->limit(self::FEED_CONTEXT_LIMIT)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($post) {
                $post->content = substr($post->content, 0, self::FEED_CONTENT_TRUNCATE);
                return $post;
            });
    }

    // ─── Standalone posts ─────────────────────────────────────────────────────

    private function generateStandalonePosts(
        Story $story,
        array $event,
        Agent $agent,
        $recentPosts,
        int $sequence,
    ): array {
        $feedContext = $recentPosts->map(fn($p) =>
            "@{$p->agent->name}: " . substr($p->content, 0, self::FEED_CONTENT_TRUNCATE)
        )->implode("\n");

        $characterSummary  = $this->compressedCharacter($agent);
        $beat              = $event['beat'] ?? 'rising_action';
        $emotionalRegister = $event['emotional_register'] ?? 'tense';
        $cliffhanger       = $event['cliffhanger'] ?? null;
        $foreshadowing     = $event['foreshadowing'] ?? null;

        $beatInstruction = match($beat) {
            'exposition'    => 'Establish your position. Measured, confident. Set the scene.',
            'inciting'      => 'React to the shock. This changes everything. Let that show.',
            'rising_action' => 'Things are escalating. Show the pressure. React. Take a position.',
            'crisis'        => 'Everything is on the line. Most urgent post you will write. Raw, direct.',
            'climax'        => 'Consequences are landing. History is being made right now.',
            default         => 'React authentically to this moment.'
        };

        $extras = '';
        if ($foreshadowing) $extras .= "Plant this subtle detail (do not state directly): {$foreshadowing}\n";
        if ($cliffhanger)   $extras .= "Leave this question hanging (do not state directly): {$cliffhanger}\n";

        $prompt = <<<PROMPT
DATE: {$event['date']}
EVENT: {$event['title']} — {$event['description']}
TONE: {$emotionalRegister}

RECENT FEED:
{$feedContext}

You are {$characterSummary}

DRAMATIC INSTRUCTION: {$beatInstruction}
{$extras}
Generate 1 post. Return JSON only:
{
  "posts": [
    { "content": "post text", "image_prompt": null, "location": null }
  ]
}
Only post what you would know on {$event['date']}. Return ONLY the JSON.
PROMPT;

        $response = $this->callClaude(null, $prompt, 400);
        $parsed   = $this->parseJson($response);
        $created  = [];

        foreach (array_slice($parsed['posts'] ?? [], 0, 1) as $postData) {
            $media = [];

            if (!empty($postData['image_prompt']) && $agent->canUse('image')) {
                try {
                    $url   = $this->imageService->generate($postData['image_prompt']);
                    $media = [['type' => 'image', 'url' => $url]];
                } catch (\Throwable $e) {
                    Log::warning('Image generation failed', ['agent_id' => $agent->id]);
                }
            }

            $post = Post::create([
                'story_id'               => $story->id,
                'agent_id'               => $agent->id,
                'timeline_event_sequence'=> $event['sequence'],
                'parent_post_id'         => null,
                'status'                 => 'standalone',
                'content'                => $postData['content'],
                'content_type'           => empty($media) ? 'text' : 'mixed',
                'media'                  => $media ?: null,
                'historical_date'        => $event['date'],
                'sequence'               => ++$sequence,
                'location_name'          => $postData['location'] ?? $agent->location,
                'latitude'               => $agent->latitude,
                'longitude'              => $agent->longitude,
                'meta'                   => [
                    'beat'              => $event['beat'] ?? null,
                    'emotional_register'=> $event['emotional_register'] ?? null,
                ],
            ]);

            $created[] = $post;
        }

        return $created;
    }

    // ─── Replies — batch decision ─────────────────────────────────────────────

    /**
     * ONE Claude call evaluates all agents at once.
     * Returns list of agent IDs that should reply.
     * Previously: 14 separate calls per post.
     * Now: 1 call per post. ~93% reduction in reply decision tokens.
     */
    private function generateReplies(
        Story $story,
        Post $parentPost,
        $allAgents,
        int $sequence,
    ): array {
        $candidates = $allAgents->where('id', '!=', $parentPost->agent_id);

        if ($candidates->isEmpty()) return [];

        // Single batch decision call
        $replyingAgentIds = $this->batchReplyDecision($parentPost, $candidates);

        if (empty($replyingAgentIds)) return [];

        $replies = [];

        foreach ($candidates->whereIn('id', $replyingAgentIds) as $agent) {
            if ($this->atCap($story)) break;

            try {
                $reply = $this->generateReply(
                    story: $story,
                    agent: $agent,
                    parentPost: $parentPost,
                    sequence: ++$sequence,
                );

                if ($reply) {
                    $replies[] = $reply;
                    $agent->appendMemory("Replied to {$parentPost->agent->name}");
                }

            } catch (\Throwable $e) {
                Log::warning('Reply generation failed', [
                    'agent_id' => $agent->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return $replies;
    }

    /**
     * Single Claude call — evaluates ALL agents against the post at once.
     * Returns array of agent IDs that should reply.
     */
    private function batchReplyDecision(Post $parentPost, $candidates): array
    {
        $agentList = $candidates->map(fn($a) =>
            "ID:{$a->id} | {$a->name} | Goals: " . implode(', ', array_slice($a->goals ?? [], 0, 2)) .
            " | Concerns: " . implode(', ', array_slice($a->concerns ?? [], 0, 2))
        )->implode("\n");

        $prompt = <<<PROMPT
Post by @{$parentPost->agent->name} on {$parentPost->historical_date}:
"{$parentPost->content}"

Which of these agents would realistically reply to this post?
Only include agents whose goals or concerns are directly relevant.
Default to none — most agents do not reply to most posts. Max 2 replies per post.

AGENTS:
{$agentList}

Return JSON only: { "reply_agent_ids": [id1, id2] }
Empty array if none should reply.
PROMPT;

        $response = $this->callClaude(
            'You are a decision engine. Return only JSON.',
            $prompt,
            80
        );

        try {
            $text   = preg_replace('/```json|```/', '', $response);
            $parsed = json_decode(trim($text), true);
            return $parsed['reply_agent_ids'] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function generateReply(Story $story, Agent $agent, Post $parentPost, int $sequence): ?Post
    {
        // Compressed character summary — not full system prompt
        $characterSummary = $this->compressedCharacter($agent);

        $prompt = <<<PROMPT
You are {$characterSummary}

Reply to this post on {$parentPost->historical_date}:
@{$parentPost->agent->name}: "{$parentPost->content}"

Max 2 sentences. In character. Return JSON only:
{ "content": "reply text", "tone": "supportive|hostile|questioning|dismissive|alarmed|informational" }
PROMPT;

        $response = $this->callClaude(null, $prompt, 150);

        try {
            $parsed = $this->parseJson($response);
        } catch (\Throwable $e) {
            return null;
        }

        if (empty($parsed['content'])) return null;

        return Post::create([
            'story_id'               => $story->id,
            'agent_id'               => $agent->id,
            'timeline_event_sequence'=> $parentPost->timeline_event_sequence,
            'parent_post_id'         => $parentPost->id,
            'status'                 => 'reply',
            'content'                => $parsed['content'],
            'content_type'           => 'text',
            'historical_date'        => $parentPost->historical_date,
            'sequence'               => $sequence,
            'location_name'          => $agent->location,
            'latitude'               => $agent->latitude,
            'longitude'              => $agent->longitude,
            'meta'                   => ['tone' => $parsed['tone'] ?? null],
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Compressed 2-line character summary.
     * Replaces full system prompt on reply calls.
     * Saves ~500-800 tokens per call.
     */
    private function compressedCharacter(Agent $agent): string
    {
        $goals    = implode(', ', array_slice($agent->goals ?? [], 0, 2));
        $concerns = implode(', ', array_slice($agent->concerns ?? [], 0, 2));

        return "{$agent->name}, {$agent->role} ({$agent->affiliation}). " .
               "Goals: {$goals}. Concerns: {$concerns}.";
    }

    private function callClaude(?string $system, string $prompt, int $maxTokens): string
    {
        $payload = [
            'model'      => $this->claudeModel,
            'max_tokens' => $maxTokens,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ];

        if ($system) {
            $payload['system'] = $system;
        }

        $response = Http::withHeaders([
            'x-api-key'         => config('services.claude.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post($this->claudeUrl, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Claude API error: ' . $response->body());
        }

        return $response->json('content.0.text') ?? '';
    }

    private function parseJson(string $text): array
    {
        $text   = preg_replace('/```json|```/', '', $text);
        $parsed = json_decode(trim($text), true);

        if (!$parsed) {
            throw new \RuntimeException('JSON parse failed: ' . $text);
        }

        return $parsed;
    }
}
