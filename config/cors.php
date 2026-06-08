<?php

return [
    // Cover all API routes including internal cron endpoints
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Allow any origin — GitHub Pages, local dev, anywhere
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400, // cache preflight for 24 hours

    // Must be false when allowed_origins is * 
    'supports_credentials' => false,
];
