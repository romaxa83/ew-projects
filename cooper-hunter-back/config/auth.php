<?php

use App\Models\Admins\Admin;
use App\Models\Dealers\Dealer;
use App\Models\OneC\Moderator;
use App\Models\Technicians\Technician;
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

        Technician::GUARD => [
            'driver' => 'passport',
            'provider' => 'technicians',
            'hash' => false,
        ],

        Moderator::GUARD => [
            'driver' => 'passport',
            'provider' => '1c_moderators',
            'hash' => false,
        ],

        Dealer::GUARD => [
            'driver' => 'passport',
            'provider' => 'dealers',
            'hash' => false,
        ],
    ],

    'member_guards' => [User::GUARD, Technician::GUARD, Dealer::GUARD],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
        'technicians' => [
            'driver' => 'eloquent',
            'model' => Technician::class,
        ],
        '1c_moderators' => [
            'driver' => 'eloquent',
            'model' => Moderator::class,
        ],
        'dealers' => [
            'driver' => 'eloquent',
            'model' => Dealer::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
        'technicians' => [
            'provider' => 'technicians',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
        'dealers' => [
            'provider' => 'dealers',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'sms' => [
        'token_lifetime' => env('SMS_TOKEN_LIFETIME', 120),
        'access_token_lifetime' => env('SMS_ACCESS_TOKEN_LIFETIME', 3600),
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
        'technicians' => [
            'id' => env('OAUTH_TECHNICIANS_CLIENT_ID'),
            'secret' => env('OAUTH_TECHNICIANS_CLIENT_SECRET'),
        ],
        '1c_moderators' => [
            'id' => env('OAUTH_1C_CLIENT_ID'),
            'secret' => env('OAUTH_1C_CLIENT_SECRET'),
        ],
        'dealers' => [
            'id' => env('OAUTH_DEALER_CLIENT_ID'),
            'secret' => env('OAUTH_DEALER_SECRET'),
        ],
    ],

    'oauth_tokens_expire_in' => env('ACCESS_TOKEN_LIFETIME', 15), //15 min
    'oauth_refresh_tokens_expire_in' => env('REFRESH_TOKEN_LIFETIME', 60), //60 min
    'oauth_remembered_refresh_tokens_expire_in' => env('REMEMBERED_REFRESH_TOKEN_LIFETIME', 60 * 24 * 30), //1 month
    'oauth_personal_access_tokens_expire_in' => env('PERSONAL_ACCESS_TOKENS_EXPIRE_IN', 1440),

];
