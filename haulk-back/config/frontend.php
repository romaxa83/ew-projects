<?php

return [
    'url' => env('APP_FRONTEND_URL'),
    'bodyshop_url' => env('APP_BODYSHOP_FRONTEND_URL'),

    'auth_url' => env('FRONTEND_AUTH_URL'),

    'payment_history_url' => env('APP_FRONTEND_URL') . '/billing/payment-history',

    'images' => [
        'email' => [
            'google_play' => env('APP_FRONTEND_URL')
                . '/'
                . env('GOOGLE_PLAY_ICON_PATH', 'assets/images/google_play.png'),
            'app_store' => env('APP_FRONTEND_URL')
                . '/'
                . env('APP_STORE_ICON_PATH', 'assets/images/app_store.png'),
            'logo_gray' => env('APP_FRONTEND_URL')
                . '/'
                . env('LOGO_GRAY_ICON_PATH', 'assets/images/logo/logo-gray.png'),
            'logo_white' => env('APP_FRONTEND_URL')
                . '/'
                . env('LOGO_WHITE_ICON_PATH', 'assets/images/logo/logo-white.png'),
        ],
    ],

    'orders_with_tag_filter_url' => env('APP_FRONTEND_URL') . '/orders?tag_id={id}',
    'users_with_tag_filter_url' => env('APP_FRONTEND_URL') . '/users?tag_id={id}',
    'trucks_with_tag_filter_url' => env('APP_FRONTEND_URL') . '/trucks?tag_id={id}',
    'trailers_with_tag_filter_url' => env('APP_FRONTEND_URL') . '/trucks?tag_id={id}',
    'trucks_with_owner_filter_url' => env('APP_FRONTEND_URL') . '/trucks?owner_id={id}',
    'trailers_with_owner_filter_url' => env('APP_FRONTEND_URL') . '/trailers?owner_id={id}',

    'bs_trucks_with_tag_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/trucks?tag_id={id}',
    'bs_trailers_with_tag_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/trucks?tag_id={id}',
    'bs_customers_with_tag_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/customers?tag_id={id}',
    'bs_inventories_with_category_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/inventories?category_id={id}',
    'bs_inventories_with_supplier_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/inventories?supplier_id={id}',
    'bs_trucks_with_customer_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/trucks?customer_id={id}',
    'bs_trailers_with_customer_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/trailers?customer_id={id}',
    'bs_order_show_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders/{id}',
    'bs_open_orders_with_truck_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?truck_id={id}',
    'bs_deleted_orders_with_truck_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?truck_id={id}&status=deleted',
    'bs_open_orders_with_trailer_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?trailer_id={id}',
    'bs_deleted_orders_with_trailer_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?trailer_id={id}&status=deleted',
    'bs_open_orders_with_inventory_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?inventory_id={id}',
    'bs_deleted_orders_with_inventory_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?inventory_id={id}&status=deleted',
    'bs_types_of_work_with_inventory_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/type-of-work?inventory_id={id}',
    'bs_open_orders_with_mechanic_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?mechanic_id={id}',
    'bs_deleted_orders_with_mechanic_filter_url' => env('APP_BODYSHOP_FORNTEND_URL') . '/orders?mechanic_id={id}&status=deleted',
];
