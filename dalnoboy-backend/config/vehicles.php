<?php

return [
    'schemas' => [
        'main' => storage_path('app/schemas/main.png'),
        'trailer' => storage_path('app/schemas/trailer.png'),
        'wheel' => [
            'on' => storage_path('app/schemas/wheel.png'),
            'off' => storage_path('app/schemas/wheel_grey.png'),
        ],
        'font' => [
            'size' => 70,
            'file' => storage_path('app/schemas/arial.ttf'),
            'color' => [
                'on' => '64646',
                'off' => 'c8c8c8',
            ],
        ],
        'add_axles' => [
            'trailer' => [
                'step' => 384,
                'spare' => 398,
                'resize' => [
                    'spare' => 217,
                    'last_axle' => 384
                ]
            ],
        ]
    ],
];
