<?php

use WezomCms\Users\Dashboard;
use WezomCms\Users\Widgets;

return [
    'sms_service' => 'esputnik', // Support: turbosms, esputnik
    'password_min_length' => 8,
    'password_default' => 'password',
    'referrals' => [
        'bonus_limit' => 10,
    ],
    'supported_social_links' => [
        'telegram',
        'instagram',
        'whatsapp',
    ],
    'supported_socials' => [
        'facebook',
        'google',
        'twitter',
    ],
    'socials' => [
        'facebook' => [
            'scopes' => ['email'],
            'fields' => ['first_name', 'last_name', 'email'],
            'fields_mapping' => [
                'name' => 'first_name',
                'surname' => 'last_name',
            ],
        ],
        'google' => [
            'fields_mapping' => [
                'name' => 'given_name',
                'surname' => 'family_name',
            ],
        ],
    ],
    'widgets' => [
        'cabinet-button' => Widgets\CabinetButton::class,
        'cabinet-menu' => Widgets\CabinetMenu::class,
        'cabinet-socials' => Widgets\CabinetSocials::class,
        'cabinet-auth-socials' => Widgets\CabinetAuthSocials::class,
    ],
    'dashboards' => [
        Dashboard\UsersDashboard::class,
        Dashboard\ReferralsDashboard::class,
        //Dashboard\ActiveUsersDashboard::class
    ],
    'oauth_client' => [
        'users' => [
            'id' => env('OAUTH_USERS_ID'),
            'secret' => env('OAUTH_USERS_SECRET'),
        ],
    ],

    'oauth_tokens_expire_in' => env('ACCESS_TOKEN_LIFETIME', 6000),
    'oauth_refresh_tokens_expire_in' => env('REFRESH_TOKEN_LIFETIME', 12000),
    'oauth_personal_access_tokens_expire_in' => env('PERSONAL_ACCESS_TOKENS_EXPIRE_IN', 525600),
];
