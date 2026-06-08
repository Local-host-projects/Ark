<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StoryController extends Controller
{
    /**
     * List all stories for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $stories = $request->user()
            ->stories()
            ->withCount(['agents', 'posts', 'spaces'])
            ->latest()
            ->paginate(20);

        return response()->json($stories);
    }

    /**
     * Create a new story.
     * Returns immediately. The cron worker handles ingestion + first event.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'sources'                 => 'required|array|min:1',
            'sources.*.type'          => 'required|in:text,url,prompt',
            'sources.*.content'       => 'required|string',
            'sources.*.schedule_hint' => 'nullable|string',
        ]);

        $story = $request->user()->stories()->create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'sources'     => $data['sources'],
            'status'      => 'pending',
        ]);

        return response()->json([
            'message' => 'Story queued. It will appear in the feed within 1–5 minutes.',
            'story'   => $story,
        ], 202);
    }

    /**
     * Get a single story with its full timeline and agents.
     */
    public function show(Request $request, Story $story): JsonResponse
    {
        $this->gate($request, $story);

        return response()->json(
            $story->load('agents')
                  ->loadCount(['posts', 'spaces'])
        );
    }

    /**
     * Delete a story and all its data.
     */
    public function destroy(Request $request, Story $story): JsonResponse
    {
        $this->gate($request, $story);
        $story->delete();
        return response()->json(['message' => 'Story deleted.']);
    }

    private function gate(Request $request, Story $story): void
    {
        abort_if(!$story->isOwned($request->user()->id), 403);
    }
}