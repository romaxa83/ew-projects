<?php

return [
    'storage' => env('IMAGE_STORAGE', 'public'),
    'original_storage' => env('IMAGE_ORIGINAL_STORAGE', 'public'),
    'models' => [
        'admin' => [
            'class' => \App\Models\Admin\Admin::class,
            'sizes' => [
                'small' => [
                    'width' => 170,
                    'height' => 170,
                    'mode' => 'resize',
                ],
                'medium' => [
                    'width' => 300,
                    'height' => 300,
                    'mode' => 'resize',
                ]
            ]
        ],
        'user' => [
            'class' => \App\Models\User\User::class,
            'sizes' => [
                'small' => [
                    'width' => 170,
                    'height' => 170,
                    'mode' => 'resize',
                ],
                'medium' => [
                    'width' => 350,
                    'height' => 350,
                    'mode' => 'resize',
                ]
            ]
        ],
        'dealership' => [
            'class' => \App\Models\Dealership\Dealership::class,
            'sizes' => [
                'small' => [
                    'width' => 170,
                    'height' => 170,
                    'mode' => 'resize',
                ],
                'medium' => [
                    'width' => 350,
                    'height' => 350,
                    'mode' => 'resize',
                ]
            ]
        ],
        'model' => [
            'class' => \App\Models\Catalogs\Car\Model::class,
            'sizes' => [
                'small' => [
                    'width' => 170,
                    'height' => 170,
                    'mode' => 'resize',
                ],
                'medium' => [
                    'width' => 350,
                    'height' => 350,
                    'mode' => 'resize',
                ]
            ]
        ],
        'brand' => [
            'class' => \App\Models\Catalogs\Car\Brand::class,
            'sizes' => [
                'small' => [
                    'width' => 170,
                    'height' => 170,
                    'mode' => 'resize',
                ],
                'medium' => [
                    'width' => 350,
                    'height' => 350,
                    'mode' => 'resize',
                ]
            ]
        ],
        'car' => [
            'class' => \App\Models\User\Car::class,
            'sizes' => [
                'small' => [
                    'width' => 170,
                    'height' => 170,
                    'mode' => 'resize',
                ],
                'medium' => [
                    'width' => 350,
                    'height' => 350,
                    'mode' => 'resize',
                ]
            ]
        ],
        'promotion' => [
            'class' => \App\Models\Promotion\Promotion::class,
            'sizes' => [
                'small' => [
                    'width' => 170,
                    'height' => 170,
                    'mode' => 'resize',
                ],
                'medium' => [
                    'width' => 350,
                    'height' => 350,
                    'mode' => 'resize',
                ]
            ]
        ],
    ],
];
