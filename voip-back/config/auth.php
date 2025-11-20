<?php

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Users\User;

return [

    'defaults' => [
        'guard' => Admin::GUARD,
        'passwords' => 'admins',
    ],

    'guards' => [
        Admin::GUARD => [
            'driver' => 'passport',
            'provider' => 'admins',
            'hash' => false,
        ],
        Employee::GUARD => [
            'driver' => 'passport',
            'provider' => 'employees',
            'hash' => false,
        ],
        User::GUARD => [
            'driver' => 'passport',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'auth_guards' => [Admin::GUARD, Employee::GUARD, User::GUARD],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
        'employees' => [
            'driver' => 'eloquent',
            'model' => Employee::class,
        ],
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],
    ],

    'passwords' => [
        'employees' => [
            'provider' => 'employees',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

    'oauth_client' => [
        'admins' => [
            'id' => env('OAUTH_ADMINS_CLIENT_ID'),
            'secret' => env('OAUTH_ADMINS_CLIENT_SECRET'),
        ],
        'employees' => [
            'id' => env('OAUTH_EMPLOYEES_CLIENT_ID'),
            'secret' => env('OAUTH_EMPLOYEES_CLIENT_SECRET'),
        ],
    ],

    'password_token_life' => 60, // min

    'oauth_tokens_expire_in' => env('ACCESS_TOKEN_LIFETIME', 6000),
    'oauth_refresh_tokens_expire_in' => env('REFRESH_TOKEN_LIFETIME', 12000),
    'oauth_personal_access_tokens_expire_in' => env('PERSONAL_ACCESS_TOKENS_EXPIRE_IN', 1440),

];
