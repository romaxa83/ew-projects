<?php

return [
    'super_admin' => [
        'email' => 'super.admin@gmail.com'
    ],
    'password' => [
        'random' => env('RANDOM_PASSWORD', true),
        'length' => 10
    ],
    // верификация
    'verify_email' => [
        'enabled' =>  true,
        'email_token_expired' => 'PT5M',     // 5 мин
    ]
];

