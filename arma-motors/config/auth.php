<?php

return [

    'defaults' => [
        'guard' => 'graph_user',
        'passwords' => 'users',
    ],

    'guards' => [
        'graph_user' => [
            'driver' => 'passport',
            'provider' => 'users',
            'hash' => false,
        ],

        'graph_admin' => [
            'driver' => 'passport',
            'provider' => 'admins',
            'hash' => false,
        ],
//        'web' => [
//            'driver' => 'session',
//            'provider' => 'users',
//        ],
//
//        'api' => [
//            'driver' => 'token',
//            'provider' => 'users',
//            'hash' => false,
//        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User\User::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin\Admin::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
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

    'oauth_tokens_expire_in' => env('ACCESS_TOKEN_LIFETIME', 6000),
    'oauth_refresh_tokens_expire_in' => env('REFRESH_TOKEN_LIFETIME', 12000),
    'oauth_personal_access_tokens_expire_in' => env('PERSONAL_ACCESS_TOKENS_EXPIRE_IN', 525600),
];
