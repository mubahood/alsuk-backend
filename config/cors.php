<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://alsukssd.com',
        'https://www.alsukssd.com',
        'http://localhost',
        'https://localhost',
        'http://127.0.0.1',
        'https://127.0.0.1',
    ],
    'allowed_origins_patterns' => [
        '/^http:\/\/localhost(:\d+)?$/',
        '/^https:\/\/localhost(:\d+)?$/',
        '/^http:\/\/127\.0\.0\.1(:\d+)?$/',
        '/^https:\/\/127\.0\.0\.1(:\d+)?$/',
        '/^http:\/\/192\.168\.\d+\.\d+(:\d+)?$/',
        '/^https:\/\/192\.168\.\d+\.\d+(:\d+)?$/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400, // 24 hours
    'supports_credentials' => true,
];
