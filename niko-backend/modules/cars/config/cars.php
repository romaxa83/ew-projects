<?php

use WezomCms\Cars\Dashboard;

return [
    'image_brand' => [
        'directory' => 'cars/brand',
        'default' => 'medium', // For admin image preview
        'sizes' => [
            'medium' => [
                'width' => 660,
                'height' => 660,
                'mode' => 'fit',
            ],
        ],
    ],
    'dashboards' => [
        Dashboard\BrandDashboard::class,
        Dashboard\ModelDashboard::class,
    ]
];
