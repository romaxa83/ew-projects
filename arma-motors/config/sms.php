<?php

return [
    // "omnicell", "array"
    'driver' => env('SMS_DRIVER', 'array'),

    'drivers' => [
        'omnicell' => [
            'url' => env('SMS_OMNICELL_URL'),
            'login' => env('SMS_OMNICELL_LOGIN'),
            'password' => env('SMS_OMNICELL_PASSWORD'),
        ],
    ],

    'enable_sender' => env('ENABLE_SMS_SENDER', false),
    // верефикация
    'verify' => [
        'code_length' => 4,                 // длина смс-кода
        'sms_token_expired' => 'PT1M',      // 1 мин
        'action_token_expired' => 'PT1H',   // 1 час
        // через сколько дней, будут удален,не использованные токены
        'old_days' => 2
    ]
];
