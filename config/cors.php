<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://alsukssd.com',
        'https://www.alsukssd.com',
        'https://app.alsukssd.com',
        'http://app.alsukssd.com',
        'http://localhost',
        'https://localhost',
        'http://127.0.0.1',
        'https://127.0.0.1',
    ],
    'allowed_origins_patterns' => [
        '/^https?:\/\/.*\.alsukssd\.com$/',
        '/^http:\/\/localhost(:\d+)?$/',
        '/^https:\/\/localhost(:\d+)?$/',
        '/^http:\/\/127\.0\.0\.1(:\d+)?$/',
        '/^https:\/\/127\.0\.0\.1(:\d+)?$/',
        '/^http:\/\/192\.168\.\d+\.\d+(:\d+)?$/',
        '/^https:\/\/192\.168\.\d+\.\d+(:\d+)?$/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
    ],
    'max_age' => 86400,
    'supports_credentials' => true,
];
