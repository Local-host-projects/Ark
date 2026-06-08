<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * GET /stories/{story}/feed
     *
     * Returns posts in chronological order (by sequence).
     * Only top-level standalone posts are returned — replies are nested inside.
     *
     * Spice of relevance:
     * - Posts from events the user has recently viewed are shown first within each date bucket
     * - Breaking posts (first post of a new event) are flagged with is_breaking: true
     * - After every 8 chronological posts, 1 high-engagement reply thread is surfaced
     *
     * Query params:
     * - after_sequence: int   — cursor for pagination
     * - date: YYYY-MM-DD      — filter to a specific historical date
     * - agent_id: int         — filter to a specific agent
     * - per_page: int         — default 20
     */
    public function index(Request $request, Story $story): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);

        $perPage = min($request->integer('per_page', 20), 50);

        $query = Post::where('story_id', $story->id)
            ->whereNull('parent_post_id') // top level only
            ->with([
                'agent:id,name,type,role,affiliation,avatar_url,location',
                'replies.agent:id,name,type,role,affiliation,avatar_url',
            ])
            ->orderBy('sequence');

        if ($request->filled('after_sequence')) {
            $query->where('sequence', '>', (int) $request->after_sequence);
        }

        if ($request->filled('date')) {
            $query->where('historical_date', $request->date);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', (int) $request->agent_id);
        }

        $posts = $query->paginate($perPage);

        // Flag breaking posts and surface beat data for frontend styling
        $seenEventSequences = [];
        $posts->getCollection()->transform(function ($post) use (&$seenEventSequences, $story) {
            $seq   = $post->timeline_event_sequence;
            $event = $story->getEvent($seq);

            $post->is_breaking        = !in_array($seq, $seenEventSequences);
            $post->beat               = $event['beat'] ?? $post->meta['beat'] ?? null;
            $post->emotional_register = $event['emotional_register'] ?? $post->meta['emotional_register'] ?? null;
            $post->event_title        = $event['title'] ?? null;
            $post->event_date         = $event['date'] ?? $post->historical_date;
            $post->cliffhanger        = $post->is_breaking ? ($event['cliffhanger'] ?? null) : null;

            if ($post->is_breaking) {
                $seenEventSequences[] = $seq;
            }

            return $post;
        });

        return response()->json([
            'story' => [
                'id'               => $story->id,
                'title'            => $story->title,
                'status'           => $story->status,
                'current_sequence' => $story->current_sequence,
                'total_events'     => $story->totalEvents(),
                'progress_percent' => $story->progressPercent(),
                'period_start'     => $story->period_start,
                'period_end'       => $story->period_end,
            ],
            'feed' => $posts,
        ]);
    }
}
