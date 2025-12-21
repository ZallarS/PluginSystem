<?php

return [
    'admin_username' => env('ADMIN_USERNAME', 'admin'),
    'admin_password' => env('ADMIN_PASSWORD', 'admin'),
    'session_timeout' => env('SESSION_TIMEOUT', 3600), // 1 час

    'csrf' => [
        'token_name' => '_token',
        'header_name' => 'X-CSRF-TOKEN',
    ],
];