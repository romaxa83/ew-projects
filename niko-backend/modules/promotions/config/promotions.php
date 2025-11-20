<?php

return [
    'image' => [
        'directory' => 'promotions',
        'default' => 'medium',
        'og_size' => 'medium',
        'sizes' => [
            'small' => [
                'width' => 420,
                'height' => 420,
                'mode' => 'resize',
            ],
            'medium' => [
                'width' => 1920,
                'height' => 1080,
                'mode' => 'resize',
            ],
        ],
    ],
    'image_ua' => [
        'directory' => 'promotions',
        'default' => 'medium',
        'sizes' => [
            'small' => [
                'width' => 420,
                'height' => 420,
                'mode' => 'resize',
            ],
            'medium' => [
                'width' => 1920,
                'height' => 1080,
                'mode' => 'resize',
            ],
        ],
    ],
    'dashboards' => [
        \WezomCms\Promotions\Dashboard\PromotionsDashboard::class,
    ]
];
