<?php

return [
    'superadmin' => [
        'first_name' => env('BS_SUPERADMIN_FIRST_NAME'),
        'last_name' => env('BS_SUPERADMIN_LAST_NAME'),
        'email' => env('BS_SUPERADMIN_EMAIL'),
        'password' => env('BS_SUPERADMIN_PASSWORD'),
    ],
    'enable_sync' => env('BS_ENABLE_SYNC', false),
    'host' => env('BS_WEBHOOK_HOST'),
    'token' => env('BS_WEBHOOK_AUTH_TOKEN'),
];
