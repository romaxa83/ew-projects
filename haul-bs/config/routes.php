<?php

$frontUrl = trim(env('FRONTEND_URL', 'http://localhost'), '/');
$frontAuthUrl = trim(env('FRONTEND_AUTH_URL', 'http://localhost'), '/');

return [
    'landing_url' => env('LANDING_URL', 'https://haulk.app/'),
    'api_url'  => env('API_URL', 'https://bs-dev-api.haulk.app'),

    // url для мобильного приложения
    'mobile' => [
        'android_app' => env('URL_ANDROID_APP', 'https://play.google.com/store/apps/details?id=app.haulk.android'),
        'ios_app' => env('URL_IOS_APP', 'https://apps.apple.com/ua/app/haulk/id1543038190?l'),
    ],

    'front' => [
        'auth' => $frontAuthUrl,
        'home' => $frontUrl,
        'forgot_password' => $frontUrl . '/forgot-password',
        'email_verification' => $frontUrl . '/email-verification',
        'set_password' => $frontAuthUrl . '/password-set',

        // supplier
        'inventories_with_supplier_filter_url' => $frontUrl . '/inventories?supplier_id={id}',
        // customer
        'trucks_with_customer_filter_url' => $frontUrl . '/trucks?customer_id={id}',
        'trailers_with_customer_filter_url' => $frontUrl . '/trailers?customer_id={id}',
        // tag
        'customers_with_tag_filter_url' => $frontUrl . '/customers?tag_id={id}',
        'trucks_with_tag_filter_url' => $frontUrl . '/trucks?tag_id={id}',
        'trailers_with_tag_filter_url' => $frontUrl . '/trucks?tag_id={id}',
        // inventory category
        'inventories_with_category_filter_url' => $frontUrl . '/inventories?category_id={id}',

        // bs orders
        'bs_order_show_url' => $frontUrl . '/orders/{id}',
        'bs_open_orders_with_truck_filter_url' => $frontUrl . '/orders?truck_id={id}',
        'bs_deleted_orders_with_truck_filter_url' => $frontUrl . '/orders?truck_id={id}&status=deleted',
        'bs_open_orders_with_trailer_filter_url' => $frontUrl . '/orders?trailer_id={id}',
        'bs_deleted_orders_with_trailer_filter_url' => $frontUrl . '/orders?trailer_id={id}&status=deleted',
        'bs_open_orders_with_inventory_filter_url' => $frontUrl . '/orders?inventory_id={id}',
        'bs_deleted_orders_with_inventory_filter_url' => $frontUrl . '/orders?inventory_id={id}&status=deleted',
        'bs_types_of_work_with_inventory_filter_url' => $frontUrl . '/type-of-work?inventory_id={id}',
        'bs_open_customer' => $frontUrl . '/customers/{id}',

        // parts orders
        'parts_order_show_url' => $frontUrl . '/parts-orders/{id}',
    ],

    // пути к картинкам для email
    'images' => [
        'email' => [
            'google_play' => $frontUrl
                . '/'
                . env('GOOGLE_PLAY_ICON_PATH', 'assets/images/google_play.png'),
            'app_store' => $frontUrl
                . '/'
                . env('APP_STORE_ICON_PATH', 'assets/images/app_store.png'),
            'logo_gray' => $frontUrl
                . '/'
                . env('LOGO_GRAY_ICON_PATH', 'assets/images/logo/logo-gray.png'),
            'logo_white' => $frontUrl
                . '/'
                . env('LOGO_WHITE_ICON_PATH', 'assets/images/logo/logo-white.png'),
        ],
    ],
];

