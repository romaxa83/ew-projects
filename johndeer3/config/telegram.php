<?php

return [
    'develop' => [
        'enable' => env('TELEGRAM_ENABLE', false),
        'env' => env('TELEGRAM_ENV', 'local'),
        'token' => env('TELEGRAM_TOKEN', ''),
        'chat_id' => env('TELEGRAM_CHAT_ID', '')
    ]
];
