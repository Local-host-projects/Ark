<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceAssignmentService
{
    private string $aethexUrl = 'https://api.aethexai.com/api/v1';

    /**
     * Assign the best available AethexAI voice to an agent.
     * Stores the voice_id in agent meta.
     * Returns the voice_id string.
     */
    public function assignVoice(Agent $agent): string
    {
        $voiceId = $this->findBestVoice($agent);

        $meta = $agent->meta ?? [];
        $meta['aethex_voice_id'] = $voiceId;
        $agent->update(['meta' => $meta]);

        return $voiceId;
    }

    /**
     * Assign voices to all agents in a story at once.
     * Called after ingestion completes.
     */
    public function assignToStoryAgents(\App\Models\Story $story): void
    {
        $agents = $story->agents()->get();

        foreach ($agents as $agent) {
            try {
                $this->assignVoice($agent);
            } catch (\Throwable $e) {
                Log::warning('Voice assignment failed for agent', [
                    'agent_id' => $agent->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Find the best matching voice for an agent based on:
     * 1. Country derived from affiliation/location
     * 2. Gender (default male for historical figures, female for female agents)
     * 3. Tags matching the agent's personality and role
     */
    public function findBestVoice(Agent $agent): string
    {
        $country = $this->deriveCountry($agent);
        $gender  = $this->deriveGender($agent);
        $tags    = $this->deriveTags($agent);

        // Try progressively relaxed queries until we get a match
        $voiceId = $this->queryVoices(country: $country, gender: $gender, tag: $tags[0] ?? null)
            ?? $this->queryVoices(country: $country, gender: $gender)
            ?? $this->queryVoices(country: $country)
            ?? $this->queryVoices(gender: $gender, tag: $tags[0] ?? null)
            ?? $this->queryVoices(gender: $gender)
            ?? 'default';

        Log::info('Voice assigned', [
            'agent'   => $agent->name,
            'country' => $country,
            'gender'  => $gender,
            'tags'    => $tags,
            'voice_id'=> $voiceId,
        ]);

        return $voiceId;
    }

    /**
     * Query the AethexAI voice catalog with filters.
     * Results are cached for 1 hour to avoid hammering the API.
     * Returns the first matching voice ID or null.
     */
    private function queryVoices(
        ?string $country = null,
        ?string $gender  = null,
        ?string $tag     = null,
    ): ?string {
        $cacheKey = "aethex_voices_{$country}_{$gender}_{$tag}";

        $voices = Cache::remember($cacheKey, 3600, function () use ($country, $gender, $tag) {
            $params = ['limit' => 50, 'language' => 'english'];

            if ($country) $params['country'] = $country;
            if ($tag)     $params['tag']     = $tag;

            $response = Http::withHeaders([
                'X-API-Key' => config('services.aethex.api_key'),
            ])->get("{$this->aethexUrl}/voices", $params);

            if (!$response->successful()) return [];

            return $response->json() ?? [];
        });

        // Filter by gender client-side since API doesn't support it as a query param
        if ($gender) {
            $voices = array_filter($voices, fn($v) => ($v['gender'] ?? '') === $gender);
        }

        $voices = array_values($voices);

        return !empty($voices) ? $voices[0]['id'] : null;
    }

    /**
     * Derive the ISO 3166-1 country code from an agent's affiliation and location.
     */
    private function deriveCountry(Agent $agent): ?string
    {
        $affiliation = strtolower($agent->affiliation ?? '');
        $location    = strtolower($agent->location ?? '');
        $combined    = $affiliation . ' ' . $location;

        $countryMap = [
            // British
            'british'         => 'GB',
            'united kingdom'  => 'GB',
            'england'         => 'GB',
            'uk'              => 'GB',
            'london'          => 'GB',
            'churchill'       => 'GB',
            'royal'           => 'GB',

            // American
            'american'        => 'US',
            'united states'   => 'US',
            'usa'             => 'US',
            'washington'      => 'US',
            'new york'        => 'US',

            // German
            'german'          => 'DE',
            'germany'         => 'DE',
            'nazi'            => 'DE',
            'reich'           => 'DE',
            'berlin'          => 'DE',
            'Wehrmacht'       => 'DE',

            // Soviet / Russian
            'soviet'          => 'RU',
            'russian'         => 'RU',
            'russia'          => 'RU',
            'ussr'            => 'RU',
            'moscow'          => 'RU',
            'red army'        => 'RU',

            // French
            'french'          => 'FR',
            'france'          => 'FR',
            'paris'           => 'FR',

            // Italian
            'italian'         => 'IT',
            'italy'           => 'IT',
            'rome'            => 'IT',
            'fascist'         => 'IT',

            // Japanese
            'japanese'        => 'JP',
            'japan'           => 'JP',
            'tokyo'           => 'JP',
            'imperial japan'  => 'JP',

            // Nigerian
            'nigerian'        => 'NG',
            'nigeria'         => 'NG',
            'lagos'           => 'NG',
            'abuja'           => 'NG',

            // Egyptian
            'egyptian'        => 'EG',
            'egypt'           => 'EG',
            'cairo'           => 'EG',

            // South African
            'south african'   => 'ZA',
            'south africa'    => 'ZA',
        ];

        foreach ($countryMap as $keyword => $code) {
            if (str_contains($combined, $keyword)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Derive gender from agent name and role.
     * Defaults to male for historical figures (most documented figures are male).
     */
    private function deriveGender(Agent $agent): string
    {
        $femaleIndicators = [
            'mrs', 'ms', 'miss', 'lady', 'queen', 'empress',
            'mother', 'sister', 'woman', 'female',
        ];

        $combined = strtolower(
            ($agent->name ?? '') . ' ' .
            ($agent->role ?? '') . ' ' .
            ($agent->background ?? '')
        );

        foreach ($femaleIndicators as $indicator) {
            if (str_contains($combined, $indicator)) {
                return 'female';
            }
        }

        return 'male';
    }

    /**
     * Derive voice tags from agent role and personality.
     * Returns up to 2 tags in priority order.
     */
    private function deriveTags(Agent $agent): array
    {
        $role    = strtolower($agent->role ?? '');
        $type    = strtolower($agent->type ?? '');
        $traits  = array_map('strtolower', $agent->goals ?? []);
        $combined = $role . ' ' . implode(' ', $traits);

        // Role-based tag mapping
        if (str_contains($combined, 'chancellor') || str_contains($combined, 'president') || str_contains($combined, 'prime minister')) {
            return ['authoritative', 'confident'];
        }
        if (str_contains($combined, 'general') || str_contains($combined, 'commander') || str_contains($combined, 'admiral')) {
            return ['authoritative', 'direct'];
        }
        if ($type === 'media' || str_contains($role, 'journalist') || str_contains($role, 'correspondent')) {
            return ['informative', 'clear'];
        }
        if ($type === 'government' || str_contains($role, 'minister') || str_contains($role, 'secretary')) {
            return ['professional', 'formal'];
        }
        if (str_contains($role, 'scientist') || str_contains($role, 'physicist')) {
            return ['analytical', 'precise'];
        }

        return ['professional'];
    }
}
