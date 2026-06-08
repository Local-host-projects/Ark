<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /stories/{story}/agents/{agent}?page=N
     * Full profile for a single agent with paginated posts.
     */
    public function show(Request $request, Story $story, Agent $agent): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);
        abort_if($agent->story_id !== $story->id, 404);

        $page  = max(1, (int) $request->get('page', 1));
        $posts = $agent->posts()
            ->whereNull('parent_post_id')
            ->with(['replies.agent:id,name,type,role,affiliation,avatar_url'])
            ->orderBy('sequence')
            ->paginate(30, ['*'], 'page', $page);

        $totalPosts   = $agent->posts()->count();
        $totalReplies = $agent->posts()->where('status', 'reply')->count();

        return response()->json([
            'agent' => array_merge($agent->toArray(), [
                'total_posts'   => $totalPosts,
                'standalone'    => $totalPosts - $totalReplies,
                'total_replies' => $totalReplies,
            ]),
            'posts' => $posts,
        ]);
    }

    /**
     * GET /stories/{story}/agents
     * List all agents in a story.
     */
    public function index(Request $request, Story $story): JsonResponse
    {
        abort_if(!$story->isOwned($request->user()->id), 403);

        $agents = $story->agents()
            ->withCount('posts')
            ->get()
            ->map(fn($agent) => [
                'id'          => $agent->id,
                'name'        => $agent->name,
                'type'        => $agent->type,
                'role'        => $agent->role,
                'affiliation' => $agent->affiliation,
                'location'    => $agent->location,
                'avatar_url'  => $agent->avatar_url,
                'posts_count' => $agent->posts_count,
            ]);

        return response()->json($agents);
    }
}
