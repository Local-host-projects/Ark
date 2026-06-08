<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\ResearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
    public function __construct(private readonly ResearchService $research) {}

    /**
     * GET /stories/{story}/research
     *
     * Research any topic related to this story.
     * Goes online via Wikipedia, synthesizes a clean brief via Claude.
     * Cached 24 hours per query.
     *
     * Query params:
     * - q: string          the topic or question (required)
     * - date: YYYY-MM-DD   historical date for context (optional)
     */
    public function index(Request $request, Story $story): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);

        $request->validate([
            'q'    => 'required|string|max:255',
            'date' => 'nullable|string',
        ]);

        $result = $this->research->research(
            query: $request->string('q'),
            historicalDate: $request->string('date') ?: null,
        );

        return response()->json($result);
    }

    /**
     * GET /stories/{story}/research/event/{sequence}
     *
     * Research a specific timeline event by its sequence number.
     * Automatically uses the event title and date as the query.
     */
    public function event(Request $request, Story $story, int $sequence): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);

        $event = $story->getEvent($sequence);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $result = $this->research->research(
            query: $event['title'] . ' ' . ($event['description'] ?? ''),
            historicalDate: $event['date'] ?? null,
        );

        return response()->json(array_merge($result, [
            'event_sequence' => $sequence,
            'event_title'    => $event['title'],
        ]));
    }

    /**
     * GET /stories/{story}/research/agent/{agent}
     *
     * Research a specific agent — who they were, their historical significance.
     */
    public function agent(Request $request, Story $story, \App\Models\Agent $agent): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);
        abort_if($agent->story_id !== $story->id, 404);

        $query = "{$agent->name} {$agent->role} {$agent->affiliation}";

        $result = $this->research->research(
            query: $query,
            historicalDate: $story->period_start,
        );

        return response()->json(array_merge($result, [
            'agent_id'   => $agent->id,
            'agent_name' => $agent->name,
        ]));
    }
}
