<?php

return [
    'sub_domain' => env('AMOCRM_SUB_DOMAIN'),
    'client_id' => env('AMOCRM_CLIENT_ID'),
    'client_secret' => env('AMOCRM_CLIENT_SECRET'),
    'redirect_uri' => env('AMOCRM_REDIRECT_URI', rtrim(env('APP_URL', ''), '/')),

    'locale' => 'ru', // AmoCRM is not multilingual that's why should be defined locale for labels.
    'queue' => 'amocrm', // Separate single-threaded queue

    'statuses' => [
        'new_order' => 46748134,
        'new_request' => 33720076,
        'preorder' => 44807734,
        'lost' => 44821054
    ],

    'fields_mapping' => [
        'order_id' => [
            'id' => 1155835
        ],
        'product_category' => [
            'id' => 977115
        ],
        'phone' => [
            'id' => 302153,
            'enum_id' => 156847,
            'enum' => 'WORK'
        ],
        'email' => [
            'id' => 302155,
            'enum_id' => 156857,
            'enum' => 'WORK'
        ],
        'delivery_method' => [
            'id' => 1161747,
            'values' => [
                'sdek-courier' => 825249,
            ]
        ],
//        'source' => [
//            'id' => 990793,
//            'values' => [
//                'from_website' => 550155
//            ]
//        ],
    ]
];