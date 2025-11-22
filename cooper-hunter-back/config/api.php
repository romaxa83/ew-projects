<?php

return [
    'one_c' => [
        'format' => [
            'date_time' => 'Y-m-d H:i:s'
        ],
        'request_uri' => [
            'dealer' => [
                'order' => [
                    'create' => env('ONE_C_DEALER_ORDER_CREATE', 'hs/dealers/addNewOrder'),
                    'update' => env('ONE_C_DEALER_ORDER_UPDATE', 'hs/dealers/addNewOrder'),
                    'packing_slip' => [
                        'update' => env('ONE_C_DEALER_ORDER_PACKING_SLIP_UPDATE', 'hs/dealers/addNewOrder'),
                    ]
                ]
            ],
            'company' => [
                'create' => env('ONE_C_COMPANY_CREATE', 'hs/dealers/addNew') ,
                'update' => env('ONE_C_COMPANY_UPDATE', 'hs/dealers/addNew') ,
            ],
            'commercial_project' => [
                'create' => env('ONE_C_COMMERCIAL_PROJECT_CREATE', 'hs/commercialProjects/add'),
                'update' => env('ONE_C_COMMERCIAL_PROJECT_UPDATE', 'hs/commercialProjects/add'),
            ],
            'payment' => [
                'card' => [
                    'add' => env('ONE_C_PAYMENT_CARD_ADD', 'developer/add-card'),
                    'delete' => env('ONE_C_PAYMENT_CARD_DELETE', 'developer/delete-card'),
                ]
            ]
        ]
    ],
];

