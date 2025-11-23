<?php

return [
    'delivery_variant' => [
        'images' => [
            'directory' => 'delivery-variants',
            'default' => 'small',
            'sizes' => [
                'small' => [
                    'width' => 300,
                    'height' => 75,
                    'mode' => 'resize',
                ],
            ],
        ],
    ],
    'payment_variant' => [
        'images' => [
            'directory' => 'payment-variants',
            'default' => 'small',
            'sizes' => [
                'small' => [
                    'width' => 300,
                    'height' => 75,
                    'mode' => 'resize',
                ],
            ],
        ],
    ],
    'sdek' => [
        'test' => env('SDEK_TEST', true),
        'id' => env('SDEK_ID'),
        'password' => env('SDEK_PASS'),
        'tariffs' => [139],
    ],
    'payment' => [
        'icon' => [
            'directory' => 'payments/icons',
            'default' => 'small',
            'sizes' => [
                'small' => [
                    'width' => 100,
                    'height' => 100,
                    'mode' => 'resize',
                ],
            ],
        ],
    ],
];
