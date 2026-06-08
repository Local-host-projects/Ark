<?php

namespace App\Http\Controllers\Web;

use App\Models\Post;
use App\Models\Agent;
use App\Models\Space;
use App\Models\Story;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\ResearchService;
use App\Services\SpaceAudioService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class MonolithController extends Controller
{
    public function home(Request $request): RedirectResponse
    {
        $firstStory = $request->user()->stories()->latest()->first();
        return $firstStory
            ? redirect()->route('feed.show', $firstStory)
            : redirect()->route('stories.index');
    }

    public function stories(Request $request): View
    {
        $stories = $request->user()->stories()
            ->withCount(['agents', 'posts', 'spaces'])
            ->latest()
            ->get();

        $currentStory = $stories->first();

        return view('stories.index', $this->shell($request, $currentStory) + [
            'stories' => $stories,
        ]);
    }

    public function createStory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prompt'      => ['required', 'string'],
        ]);

        $story = $request->user()->stories()->create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'sources'     => [[
                'type'    => 'prompt',
                'content' => $data['prompt'],
            ]],
            'status'      => 'pending',
        ]);

        return redirect()->route('stories.index')
            ->with('status', 'Story queued. The first posts will appear in 1–5 minutes.');
    }

    public function deleteStory(Request $request, Story $story): RedirectResponse
    {
        $this->ownStory($request, $story);
        $story->delete();
        return redirect()->route('stories.index')->with('status', 'Story deleted.');
    }

    public function feed(Request $request, Story $story): View
    {
        $this->ownStory($request, $story);

        $posts = Post::where('story_id', $story->id)
            ->whereNull('parent_post_id')
            ->with(['agent', 'replies.agent'])
            ->orderBy('sequence')
            ->paginate(20);

        $posts->getCollection()->transform(function (Post $post) use ($story) {
            $event = $story->getEvent($post->timeline_event_sequence);
            $post->event = $event;
            return $post;
        });

        return view('feed.show', $this->shell($request, $story) + [
            'story' => $story->fresh(['agents']),
            'posts' => $posts,
            'nextEvent' => $story->nextEvent(),
        ]);
    }

    public function timeline(Request $request, Story $story): View
    {
        $this->ownStory($request, $story);

        return view('timeline.show', $this->shell($request, $story) + [
            'story' => $story,
            'timeline' => collect($story->timeline ?? [])->sortBy('sequence')->values(),
        ]);
    }

    public function agents(Request $request, Story $story): View
    {
        $this->ownStory($request, $story);

        return view('agents.index', $this->shell($request, $story) + [
            'story' => $story,
            'agents' => $story->agents()->withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function profile(Request $request, Story $story, Agent $agent): View
    {
        $this->ownStory($request, $story);
        abort_unless($agent->story_id === $story->id, 404);

        $posts = $agent->posts()->with(['replies.agent'])->paginate(20);

        return view('agents.show', $this->shell($request, $story) + [
            'story' => $story,
            'agent' => $agent,
            'posts' => $posts,
        ]);
    }

    public function spaces(Request $request, Story $story): View
    {
        $this->ownStory($request, $story);
        $spaces = $story->spaces()->latest('unlocks_at_sequence')->get();

        return view('spaces.index', $this->shell($request, $story) + [
            'story' => $story,
            'spaces' => $spaces,
        ]);
    }

    public function space(Request $request, Story $story, Space $space): View
    {
        $this->ownStory($request, $story);
        abort_unless($space->story_id === $story->id, 404);

        $playlist = [];
        if ($space->audio_url && str_contains($space->audio_url, '/storage/')) {
            $relative = ltrim(\Illuminate\Support\Str::after($space->audio_url, '/storage/'), '/');
            if (Storage::disk('public')->exists($relative)) {
                $playlist = json_decode(Storage::disk('public')->get($relative), true) ?: [];
            }
        }

        return view('spaces.show', $this->shell($request, $story) + [
            'story' => $story,
            'space' => $space,
            'playlist' => $playlist,
            'agents' => $space->agents()->keyBy('id'),
        ]);
    }

    public function generateSpace(Request $request, Story $story, Space $space, SpaceAudioService $audio): RedirectResponse
    {
        $this->ownStory($request, $story);
        abort_unless($space->story_id === $story->id, 404);

        try {
            $audio->generate($space);
        } catch (\Throwable $e) {
            return back()->withErrors(['space' => 'Space generation failed: '.$e->getMessage()]);
        }

        return redirect()->route('spaces.show', [$story, $space])->with('status', 'Space audio generated.');
    }

    public function research(Request $request, Story $story, ResearchService $research): View
    {
        $this->ownStory($request, $story);

        $query = $request->string('q')->toString();
        $result = null;

        if ($query !== '') {
            $result = $research->research($query, $request->string('date')->toString() ?: null);
        }

        return view('research.show', $this->shell($request, $story) + [
            'story' => $story,
            'query' => $query,
            'result' => $result,
        ]);
    }

    public function post(Request $request, Story $story, Post $post): View
    {
        $this->ownStory($request, $story);
        abort_unless($post->story_id === $story->id, 404);

        $post->load(['agent', 'replies.agent', 'parent.agent']);
        $event = $story->getEvent($post->timeline_event_sequence);

        return view('posts.show', $this->shell($request, $story) + [
            'story' => $story,
            'post' => $post,
            'event' => $event,
        ]);
    }

    private function ownStory(Request $request, Story $story): void
    {
        abort_unless($story->isOwned($request->user()->id), 403);
    }

    private function shell(Request $request, ?Story $currentStory = null): array
    {
        return [
            'storyList' => $request->user()->stories()->latest()->get(),
            'currentStory' => $currentStory,
        ];
    }
}