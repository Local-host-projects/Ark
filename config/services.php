<?php

return [
        'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
    ],
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
    'cron_secret' => env('CRON_SECRET'),

    'aethex' => [
        'api_key' => env('AETHEX_API_KEY'),
        'url'     => 'https://api.aethexai.com/api/v1',
    ],
];
