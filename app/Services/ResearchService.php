<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResearchService
{
    private string $claudeUrl   = 'https://api.anthropic.com/v1/messages';
    private string $claudeModel = 'claude-sonnet-4-20250514';

    /**
     * Research a topic — go online, fetch content, return a clean summary.
     * Cached for 24 hours per query so the same event isn't re-fetched repeatedly.
     */
    public function research(string $query, ?string $historicalDate = null): array
    {
        $cacheKey = 'research_' . md5($query . $historicalDate);

        return Cache::remember($cacheKey, 86400, function () use ($query, $historicalDate) {
            // Step 1: Build search queries
            $searchQueries = $this->buildSearchQueries($query, $historicalDate);

            // Step 2: Fetch content from web
            $sources = $this->fetchSources($searchQueries);

            if (empty($sources)) {
                return [
                    'query'   => $query,
                    'summary' => 'No research results found for this topic.',
                    'sources' => [],
                    'facts'   => [],
                ];
            }

            // Step 3: Synthesize into a clean research brief via Claude
            $brief = $this->synthesize($query, $historicalDate, $sources);

            return [
                'query'            => $query,
                'historical_date'  => $historicalDate,
                'summary'          => $brief['summary'],
                'key_facts'        => $brief['key_facts'],
                'deeper_questions' => $brief['deeper_questions'],
                'sources'          => array_map(fn($s) => [
                    'title' => $s['title'],
                    'url'   => $s['url'],
                ], $sources),
            ];
        });
    }

    // ─── Search ───────────────────────────────────────────────────────────────

    private function buildSearchQueries(string $query, ?string $date): array
    {
        $queries = [$query];

        if ($date) {
            $year = substr($date, 0, 4);
            $queries[] = "{$query} {$year} history";
            $queries[] = "{$query} historical significance";
        }

        return array_slice($queries, 0, 2);
    }

    private function fetchSources(array $queries): array
    {
        $sources = [];

        foreach ($queries as $query) {
            try {
                // Use Wikipedia API as primary research source
                // Clean, reliable, license-free content
                $wikiResults = $this->searchWikipedia($query);
                $sources = array_merge($sources, $wikiResults);

                if (count($sources) >= 1) break;

            } catch (\Throwable $e) {
                Log::warning('Research source fetch failed', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return array_slice($sources, 0, 1);
    }

    private function searchWikipedia(string $query): array
    {
        // Wikipedia OpenSearch API
        $searchResponse = Http::timeout(10)->get('https://en.wikipedia.org/w/api.php', [
            'action'   => 'opensearch',
            'search'   => $query,
            'limit'    => 3,
            'format'   => 'json',
            'namespace'=> 0,
        ]);

        if (!$searchResponse->successful()) return [];

        $data   = $searchResponse->json();
        $titles = $data[1] ?? [];
        $urls   = $data[3] ?? [];

        $sources = [];

        foreach ($titles as $index => $title) {
            $url = $urls[$index] ?? null;
            if (!$url) continue;

            // Fetch the actual Wikipedia article summary
            $summaryResponse = Http::timeout(10)->get('https://en.wikipedia.org/w/api.php', [
                'action'      => 'query',
                'titles'      => $title,
                'prop'        => 'extracts',
                'exintro'     => true,
                'explaintext' => true,
                'format'      => 'json',
            ]);

            if (!$summaryResponse->successful()) continue;

            $pages   = $summaryResponse->json('query.pages') ?? [];
            $page    = array_values($pages)[0] ?? null;
            $extract = $page['extract'] ?? null;

            if (!$extract) continue;

            $sources[] = [
                'title'   => $title,
                'url'     => $url,
                'content' => substr($extract, 0, 1500),
            ];
        }

        return $sources;
    }

    // ─── Synthesis ────────────────────────────────────────────────────────────

    private function synthesize(string $query, ?string $date, array $sources): array
    {
        $sourceText = collect($sources)->map(fn($s) =>
            "SOURCE: {$s['title']}\n{$s['content']}"
        )->implode("\n\n---\n\n");

        $dateContext = $date ? "The user is asking about this in the context of {$date}." : '';

        $prompt = <<<PROMPT
A user wants to learn more about: "{$query}"
{$dateContext}

Here is research content from Wikipedia:

{$sourceText}

Return a JSON research brief:
{
  "summary": "3-4 sentence plain-English explanation of what this is and why it matters historically",
  "key_facts": [
    "Specific fact 1",
    "Specific fact 2",
    "Specific fact 3",
    "Specific fact 4",
    "Specific fact 5"
  ],
  "deeper_questions": [
    "An interesting follow-up question the user might want to explore",
    "Another angle worth understanding",
    "A broader context question"
  ]
}

Rules:
- Write for a curious non-expert
- Keep the summary conversational and engaging
- Key facts should be specific and surprising where possible
- Deeper questions should spark genuine curiosity
- Return ONLY the JSON
PROMPT;

        $response = Http::withHeaders([
            'x-api-key'         => config('services.claude.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(20)->post($this->claudeUrl, [
            'model'      => $this->claudeModel,
            'max_tokens' => 1000,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        if (!$response->successful()) {
            return [
                'summary'          => 'Research synthesis unavailable.',
                'key_facts'        => [],
                'deeper_questions' => [],
            ];
        }

        $text   = preg_replace('/```json|```/', '', $response->json('content.0.text'));
        $parsed = json_decode(trim($text), true);

        return $parsed ?? [
            'summary'          => 'Research synthesis unavailable.',
            'key_facts'        => [],
            'deeper_questions' => [],
        ];
    }
}
