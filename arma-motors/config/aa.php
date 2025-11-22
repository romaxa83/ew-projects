<?php

return [
    'access' => [
        'enable' => env('AA_ACCESS_ENABLE', false),
        'login' => env('AA_LOGIN'),
        'password' => env('AA_PASSWORD')
    ],
    // данные для запроса к ним
    'to' => [
        'url' => env('AA_TO_URL', false),
        'token' => env('AA_TO_AUTH_TOKEN'),
    ],
    'old_days' => 30,
    'request' => [
        'accept_agreement' => [
            'path' => '/mdm/hs/maapi/order',
            'test' => false // использовать тестовые данные при запросе
        ],
        'create_car' => [
            'path' => '/mdm/hs/maapi/auto',
        ],
        'create_order' => [
            'path' => '/mdm/hs/maapi/service',
            "test" => false
        ],
        'create_user' => [
            'path' => '/mdm/hs/maapi/user',
        ],
        'get_act' => [
            'path' => '/mdm/hs/maapi/order?request=',
        ],
        'get_car' => [
            'path' => '/mdm/hs/maapi/auto?',
        ],
        'get_invoice' => [
            'path' => '/mdm/hs/maapi/invoice?request=',
        ],
        'get_user_by_phone' => [
            'path' => '/mdm/hs/maapi/user?phone=',
        ],
        'update_user' => [
            'path' => '/mdm/hs/maapi/user',
        ],
    ]
];

