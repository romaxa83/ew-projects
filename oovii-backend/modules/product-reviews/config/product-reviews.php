<?php

use WezomCms\ProductReviews\Dashboards;
use WezomCms\ProductReviews\Widgets;

return [
    'widgets' => [
        'product-reviews:latest' => Widgets\LatestReviews::class,
    ],
    'dashboards' => [
        Dashboards\ProductReviewsDashboard::class,
        //Dashboards\ProductReviewsNoPublishDashboard::class,
    ]
];
