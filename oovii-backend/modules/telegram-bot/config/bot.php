<?php


return [
    'telegram_use' => env('TELEGRAM_USE', false),
    'telegram_token' => env('TELEGRAM_TOKEN', false),
    'telegram_chat_id' => env('TELEGRAM_CHAT_ID', false),
    'telegram_env' => env('TELEGRAM_ENV', 'local'),
    'telegram_level' => env('TELEGRAM_LEVEL', 'important'),
    'project' => env('APP_NAME', 'Laravel'),
];

