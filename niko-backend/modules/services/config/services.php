<?php

use WezomCms\Services\Dashboards;

return [
    'use_groups' => true,
    'images' => [
        'directory' => 'services',
        'default' => 'medium',
        'sizes' => [
            'medium' => [
                'width' => 660,
                'height' => 660,
                'mode' => 'resize',
            ],
        ],
    ],
    'dashboards' => [
        Dashboards\ServicesDashboard::class,
    ]
];
