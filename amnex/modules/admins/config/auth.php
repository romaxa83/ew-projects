<?php

use Wezom\Admins\Models\Admin;

return [
    'guards' => [
        Admin::GUARD => [
            'driver' => 'sanctum',
            'provider' => 'admins',
        ],
    ],
    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
    ],
    'admin_revoke_all_token' => env('ADMIN_REVOKE_ALL_TOKEN', false),
    'admin_access_token_lifetime' => env('ADMIN_ACCESS_TOKEN_LIFETIME', 1440), // 24 hours
    'admin_refresh_token_lifetime' => env('ADMIN_REFRESH_TOKEN_LIFETIME', 4320), // 72 hours
    'admin_password_set_link_expires_in' => env('ADMIN_PASSWORD_SET_LINK_EXPIRES_IN', 1440), // 1 day
    'admin_email_verification_link_expires_in' => env('ADMIN_EMAIL_VERIFICATION_LINK_EXPIRES_IN', 30), // 30 minutes
];
