<?php

use Wezom\Users\Models\User;

return [
    'defaults' => [
        'guard' => User::GUARD,
    ],

    'guards' => [
        User::GUARD => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
    ],

    'user_access_token_lifetime' => env('USER_ACCESS_TOKEN_LIFETIME', 1440), // 24 hours
    'user_refresh_token_lifetime' => env('USER_REFRESH_TOKEN_LIFETIME', 4320), // 72 hours

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
