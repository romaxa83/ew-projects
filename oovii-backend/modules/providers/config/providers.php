<?php

use WezomCms\Providers\Dashboard;

return [
    'dashboards' => [
        Dashboard\ProvidersDashboard::class,
    ],
    'time_format' => [
        'created_at' => [
            'admin_table' => 'Y-m-d',
            'import' => 'Y-m-d',
            'api' => 'Y-m-d'
        ]
    ]
];

