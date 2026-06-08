<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    /**
     * GET /stories/{story}/spaces
     *
     * Returns all spaces for a story.
     * Each space carries an is_unlocked flag based on the story's current_sequence.
     * Locked spaces show title only — no audio URL until unlocked.
     */
    public function index(Request $request, Story $story): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);

        $spaces = $story->spaces()
            ->orderBy('unlocks_at_sequence')
            ->get()
            ->map(fn($space) => $this->formatSpace($space, $story));

        return response()->json([
            'current_sequence' => $story->current_sequence,
            'spaces'           => $spaces,
        ]);
    }

    /**
     * GET /stories/{story}/spaces/{space}
     *
     * Fetch a single space.
     * Returns the audio_url and playlist if unlocked.
     * Returns 403 if the user has not reached this point in the timeline yet.
     */
    public function show(Request $request, Story $story, Space $space): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);
        abort_if($space->story_id !== $story->id, 404);

        if (!$space->isUnlockedFor($story)) {
            return response()->json([
                'message'             => 'This space unlocks when you reach this point in the timeline.',
                'unlocks_at_sequence' => $space->unlocks_at_sequence,
                'current_sequence'    => $story->current_sequence,
            ], 403);
        }

        if ($space->status !== 'ready') {
            return response()->json([
                'message' => 'This space is still being generated. Check back shortly.',
                'status'  => $space->status,
            ], 202);
        }

        return response()->json($this->formatSpace($space, $story, detailed: true));
    }

    private function formatSpace(Space $space, Story $story, bool $detailed = false): array
    {
        $isUnlocked = $space->isUnlockedFor($story);
        $isReady    = $space->status === 'ready';

        $data = [
            'id'                  => $space->id,
            'title'               => $space->title,
            'description'         => $space->description,
            'historical_date'     => $space->historical_date,
            'unlocks_at_sequence' => $space->unlocks_at_sequence,
            'is_unlocked'         => $isUnlocked,
            'status'              => $space->status,
            'duration_seconds'    => $space->duration_seconds,
            'agent_ids'           => $space->agent_ids,
        ];

        // Only expose audio if unlocked and ready
        if ($isUnlocked && $isReady) {
            $data['audio_url'] = $space->audio_url;

            if ($detailed) {
                // The audio_url for spaces is a playlist.json path
                // Return the full playlist for sequential playback
                $data['agents'] = $space->agents()
                    ->map(fn($a) => [
                        'id'         => $a->id,
                        'name'       => $a->name,
                        'role'       => $a->role,
                        'avatar_url' => $a->avatar_url,
                    ]);
            }
        }

        return $data;
    }
}
