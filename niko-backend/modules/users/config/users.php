<?php

use WezomCms\Users\Dashboard;

return [
    'use_sms_sender' => env('USE_SMS_SENDER', false), // использовать sms-sender
    'sms_token_length' => 6, // длина sms-кода
    'sms_token_expire' => 60, // время жизни токена, сек
    'sms_service' => 'esputnik', // Support: turbosms, esputnik

    'token_expire' => 2, // время жизни токена, дней
    'token_refresh_expire' => 30, // время жизни токена, дней

    'password_min_length' => 8,
    'quantity_car' => 15, // кол-во машин для одного пользователя
    'dashboards' => [
        Dashboard\UsersDashboard::class,
        Dashboard\LoyaltyDashboard::class,
        //Dashboard\ActiveUsersDashboard::class
    ],

    'oauth_client' => [
        'users' => [
            'id' => env('OAUTH_USERS_CLIENT_ID'),
            'secret' => env('OAUTH_USERS_CLIENT_SECRET'),
        ],
    ],

    'oauth_tokens_expire_in' => env('ACCESS_TOKEN_LIFETIME', 6000),
    'oauth_refresh_tokens_expire_in' => env('REFRESH_TOKEN_LIFETIME', 12000),
    'oauth_personal_access_tokens_expire_in' => env('PERSONAL_ACCESS_TOKENS_EXPIRE_IN', 525600),
];
