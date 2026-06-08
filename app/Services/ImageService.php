<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    private string $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-preview-image-generation:generateContent';

    public function generate(string $prompt): string
    {
        $enriched = "{$prompt}. Style: historical documentary, period-accurate, realistic. Black and white if pre-1950s.";

        $response = Http::timeout(30)->post(
            "{$this->geminiUrl}?key=" . config('services.gemini.api_key'),
            [
                'contents'         => [['parts' => [['text' => $enriched]]]],
                'generationConfig' => ['responseModalities' => ['TEXT', 'IMAGE']],
            ]
        );

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini image failed: ' . $response->body());
        }

        foreach ($response->json('candidates.0.content.parts') ?? [] as $part) {
            if (isset($part['inlineData']['data'])) {
                return $this->store($part['inlineData']['data'], $part['inlineData']['mimeType'] ?? 'image/png');
            }
        }

        throw new \RuntimeException('No image returned from Gemini');
    }

    private function store(string $base64, string $mimeType): string
    {
        $ext      = match($mimeType) { 'image/jpeg' => 'jpg', 'image/webp' => 'webp', default => 'png' };
        $filename = 'generated/' . Str::uuid() . '.' . $ext;
        Storage::disk('public')->put($filename, base64_decode($base64));
        return Storage::disk('public')->url($filename);
    }
}
