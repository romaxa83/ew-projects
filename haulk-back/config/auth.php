<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
            'hash' => false,
        ],

        'api_admin' => [
            'driver' => 'passport',
            'provider' => 'admins',
            'hash' => false,
        ],
//
//        'mobile' => [
//            'driver' => 'mobile',
//            'provider' => 'sales',
//        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Users\User::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admins\Admin::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => env('PASSWORD_RESET_TOKEN_LIFETIME', 600),
        ],
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_resets',
            'expire' => env('PASSWORD_RESET_TOKEN_LIFETIME', 600),
        ],
    ],

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
