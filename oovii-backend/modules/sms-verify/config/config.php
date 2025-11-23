<?php


return [

    'sender' => [
        'enable' => env('SMS_SENDER_ENABLE', false),
        'driver' => env('SMS_DRIVER', 'array'),

        'drivers' => [
            'kazinfoteh' => [
                'url' => env('SMS_KAZ_URL'),
                'login' => env('SMS_KAZ_LOGIN'),
                'password' => env('SMS_KAZ_PASSWORD'),
            ],
        ],
    ],

    'verify' => [
        'code_length' => 4,                 // длина смс-кода
        'sms_token_expired' => 'PT5M',      // 1 мин
        'action_token_expired' => 'PT1H',   // 1 час
        // через сколько дней, будут удален,не использованные токены
        'old_days' => 2,
        // настройки для тестового телефона
        'dev' => [
            'enable' => env('DEV_SMS_VERIFY', false),
            'code' => '0000',     // код для тестового номера
            'symbol' => 1,      // символ в конце тел. который будет считать тел. как тестовый
            'phones' => explode(',', env('DEV_PHONES', '')),
        ],
        'constant_dev_phone' => [
            'phone' => '+70990001111',
            'code' => '9999'
        ]
    ],
];

