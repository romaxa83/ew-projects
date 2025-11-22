<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'usps' => [
        'url' => env('USPS_URL', 'https://api.usps.com'),
        'client_key' => env('USPS_CLIENT_KEY', 'client_key'),
        'client_secret' => env('USPS_CLIENT_SECRET', 'client_secret'),
        'logging' => env('USPS_LOGGING', false),
    ],
    'ups' => [
        'url' => env('UPS_URL', 'https://onlinetools.ups.com'),
        'url_sandbox' => env('UPS_SANDBOX_URL', 'https://wwwcie.ups.com'),
        'sandbox' => env('UPS_SANDBOX', true),
        'client_key' => env('UPS_CLIENT_ID', 'client_key'),
        'client_secret' => env('UPS_CLIENT_SECRET', 'client_secret'),
        'logging' => env('UPS_LOGGING', false),
        'leave_only_code' => ['01', '03', '12'],
    ],
    'fedex' => [
        'url' => env('FEDEX_URL', 'https://apis.fedex.com'),
        'url_sandbox' => env('FEDEX_SANDBOX_URL', 'https://apis-sandbox.fedex.com'),
        'sandbox' => env('FEDEX_SANDBOX', true),
        'client_key' => env('FEDEX_CLIENT_KEY', 'client_key'),
        'client_secret' => env('FEDEX_CLIENT_SECRET', 'client_secret'),
        'client_rate_key' => env('FEDEX_RATE_CLIENT_KEY', ''),
        'client_rate_secret' => env('FEDEX_RATE_CLIENT_SECRET', ''),
        'client_rate_account' => env('FEDEX_RATE_CLIENT_ACCOUNT', ''),
        'logging' => env('FEDEX_LOGGING', false),
        'leave_only_code' => ['EP1000000003', 'EP1000000134'],
    ],
];
