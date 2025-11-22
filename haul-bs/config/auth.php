<?php

use App\Foundations\Modules\Permission\Models\Role;
use App\Models\Users\User;

return [

    'defaults' => [
        'guard' => Role::GUARD_USER,
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        Role::GUARD_USER => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
//        Role::GUARD_ADMIN => [
//            'driver' => 'passport',
//            'provider' => 'users',
//        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
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
