<?php

use App\Models\Admins\Admin;
use App\Models\Users\User;

return [

    'defaults' => [
        'guard' => User::GUARD,
        'passwords' => 'users',
    ],

    'guards' => [
        User::GUARD => [
            'driver' => 'passport',
            'provider' => 'users',
            'hash' => false,
        ],

        Admin::GUARD => [
            'driver' => 'passport',
            'provider' => 'admins',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

    'oauth_client' => [
        'users' => [
            'id' => env('OAUTH_USERS_CLIENT_ID'),
            'secret' => env('OAUTH_USERS_CLIENT_SECRET'),
        ],

        'admins' => [
            'id' => env('OAUTH_ADMINS_CLIENT_ID'),
            'secret' => env('OAUTH_ADMINS_CLIENT_SECRET'),
        ],
    ],

    'oauth_tokens_expire_in' => env('ACCESS_TOKEN_LIFETIME', 30),
    'oauth_refresh_tokens_expire_in' => env('REFRESH_TOKEN_LIFETIME', 525600),
    'oauth_personal_access_tokens_expire_in' => env('PERSONAL_ACCESS_TOKENS_EXPIRE_IN', 1440),

];
