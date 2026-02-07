<?php

return [
    'paths' => ['api/*', '*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000', 'localhost:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Set-Cookie', 'X-CSRF-TOKEN', 'Authorization'],
    'max_age' => 0,
    'supports_credentials' => true,
];