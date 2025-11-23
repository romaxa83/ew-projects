<?php

return [
    'project' => env('APP_NAME', ''),
    'env' => env('APP_ENV', 'local'),
    'error_handler' => [
        'enabled' => env('TELEGRAM_ERROR_ENABLED', false),
        'token' => env('TELEGRAM_ERROR_TOKEN'),
        'chat_id' => env('TELEGRAM_ERROR_CHAT_ID'),
    ],
];
