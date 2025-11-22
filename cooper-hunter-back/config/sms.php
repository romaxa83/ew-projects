<?php

return [
    'default' => env('SMS_DRIVER', 'clicksend'),

    'drivers' => [
        'clicksend' => [
            'transport' => 'clicksend',
            'source' => env('SMS_SOURCE', 'cooper_hunter_api'),
        ],

        'array' => [
            'transport' => 'array',
        ],
    ]
];
