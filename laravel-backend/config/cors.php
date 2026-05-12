<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter([
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ]),
    // Aktsepteeri vite varuporte (5174, 5175, ...) kui 5173 on hõivatud.
    'allowed_origins_patterns' => [
        '#^https?://(localhost|127\.0\.0\.1):51[7-9][0-9]$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
