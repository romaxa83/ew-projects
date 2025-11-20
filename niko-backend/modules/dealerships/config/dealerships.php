<?php

return [
    'images' => [
        'directory' => 'dealerships',
        'default' => 'small',
        'sizes' => [
            'small' => [
                'width' => 420,
                'height' => 420,
                'mode' => 'resize',
            ],
            'medium' => [
                'width' => 660,
                'height' => 660,
                'mode' => 'resize',
            ],
            'original' => [
//                'width' => 320,
//                'height' => 320,
//                'mode' => 'resize',
            ]
        ],
    ],
    'dashboards' => [
        \WezomCms\Dealerships\Dashboard\DealershipsDashboard::class,
    ]

];
