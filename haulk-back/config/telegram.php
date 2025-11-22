<?php

return [
    'project' => env('APP_NAME', ''),
    'enabled' => env('TELEGRAM_ENABLED', false),
    'env' => env('APP_ENV', 'local'),
    'token' => env('TELEGRAM_TOKEN', ''),
    'chat_id' => env('TELEGRAM_CHAT_ID', '')
];
