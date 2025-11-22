<?php

return [
    /**
     * Limit by user or ip
     */
    'limit_by' => env('ROUTES_LIMIT_BY', 'ip'),
    'rates' => [
        'api' => env('ROUTES_RATES_API', 300),
    ],
    'front' => [
        'home' => env('FRONT_URL') . '/home',
        'not_found' => env('FRONT_URL') . '/404',
        'thank_you' => env('FRONT_URL') . '/thank-you',
        'admin_login' => env('FRONT_URL') . '/admin/login',
        'admin_lk' => env('FRONT_URL'). '/',
    ]
];
