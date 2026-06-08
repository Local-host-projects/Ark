<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * GET /stories/{story}/posts/{post}
     * Single post with replies, beat data, event context.
     */
    public function show(Request $request, Story $story, Post $post): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);
        abort_if($post->story_id !== $story->id, 404);

        $post->load([
            'agent:id,name,type,role,affiliation,avatar_url,location,latitude,longitude',
            'replies.agent:id,name,type,role,affiliation,avatar_url',
        ]);

        // Attach event context
        $event = $story->getEvent($post->timeline_event_sequence);
        $post->event_title        = $event['title'] ?? null;
        $post->event_date         = $event['date'] ?? $post->historical_date;
        $post->beat               = $event['beat'] ?? $post->meta['beat'] ?? null;
        $post->emotional_register = $event['emotional_register'] ?? $post->meta['emotional_register'] ?? null;
        $post->cliffhanger        = $event['cliffhanger'] ?? null;
        $post->is_breaking        = true; // single post view always shows full context

        return response()->json($post);
    }
}
