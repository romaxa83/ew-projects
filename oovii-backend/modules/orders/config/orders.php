<?php

use WezomCms\Orders\Dashboards;
use WezomCms\Orders\Widgets;

return [
    'widgets' => [
        'orders:product-delivery-and-payment-text' => Widgets\ProductText::class,
        'orders:cart:header-button' => Widgets\Cart\HeaderButton::class,
        'orders:cart:header-mobile-button' => Widgets\Cart\HeaderMobileButton::class,
    ],
    'dashboards' => [
        Dashboards\OrdersDashboard::class,
        // Dashboards\NewOrdersDashboard::class
    ],
];
