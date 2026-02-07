<?php

return [
    'paths' => ['api/*', '*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter([
        'http://localhost:3000',
        'http://localhost:5173', // Vite default port
        'localhost:3000',
        env('FRONTEND_URL'), // Your Railway frontend URL
    ]),
    'allowed_origins_patterns' => [
        '/^https?:\/\/.*\.up\.railway\.app$/', // Allow all Railway subdomains
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Set-Cookie', 'X-CSRF-TOKEN', 'Authorization'],
    'max_age' => 0,
    'supports_credentials' => true,
];