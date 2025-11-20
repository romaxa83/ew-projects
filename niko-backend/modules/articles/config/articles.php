<?php

use WezomCms\Articles\Dashboards;
use WezomCms\Articles\Widgets;

return [
    'use_groups' => true,
    'images' => [
        'directory' => 'articles',
        'default' => 'medium', // For admin image preview
        'sizes' => [
            'medium' => [
                'width' => 420,
                'height' => 420,
                'mode' => 'fit'
            ],
            'main_page' => [
                'width' => 660,
                'height' => 660,
                'mode' => 'fit',
            ],
        ],
    ],
    'sitemap' => [
        'articles' => true, // Enable/disable render links to all published articles in sitemap page.
    ],
    'widgets' => [
        'articles:latest' => Widgets\Latest::class,
    ],
    'dashboards' => [
        Dashboards\ArticlesDashboard::class,
    ]
];
