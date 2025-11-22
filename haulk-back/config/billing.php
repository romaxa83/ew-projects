<?php

return [
    'info_email' => env('BILLING_INFO_EMAIL', 'billing@haulk.app'),
    'failed_payment_cancel_subscription_after_days' => 30,
    'gps' => [
        'access_info_till_at' => env('GPS_ACCESS_INFO_TILL_AT', 30), //days
        'price' => env('GPS_PRICE_PER_DAY', 1.5), // $, per day
    ],
    'restricted_access_exception_urls' => [
        '*/billing/*',
        '*/profile/*',
        '*/change-language*',

        '*/orders/*/vehicles/*/inspect-vin',
        '*/orders/*/vehicles/*/inspect-pickup-damage',
        '*/orders/*/vehicles/*/inspect-delivery-damage',
        '*/orders/*/vehicles/*/inspect-pickup-exterior',
        '*/orders/*/vehicles/*/inspect-delivery-exterior',
        '*/orders/*/vehicles/*/delete-pickup-photo',
        '*/orders/*/vehicles/*/delete-delivery-photo',
        '*/orders/*/vehicles/*/inspect-pickup-interior',
        '*/orders/*/vehicles/*/inspect-delivery-interior',
        '*/orders/*/pickup-signature',
        '*/orders/*/delivery-signature',
        '*/orders/*/add-payment-data',
    ],
    'permission_groups_masked' => [
        'roles',
        'billing',
        'profile',
        'gps-menu',
    ],
    'invoices' => [
        'max_charge_attempts' => 3,
        'process_per_cycle' => 10,
        'purge_after_days' => 365,
    ],
    'providers' => [
        'driver' => env('AUTHORIZE_NET_DRIVER', 'live'),
        'authorize_net' => [
            'sandbox' => [
                'api_url' => 'https://apitest.authorize.net/xml/v1/request.api',
                'name' => '4tg69xLFZ6',
                'transactionKey' => '7V88T46s7Kgx6yGc',
                'currency_code_usd' => 'USD',
                'merchant_customer_id_prefix' => env('MERCHANT_CUSTOMER_ID_PREFIX', '')
            ],
            'live' => [
                'api_url' => 'https://api.authorize.net/xml/v1/request.api',
                'name' => '6Dx78c2WQ',
                'transactionKey' => '6X6c96Dt293K38xh',
                'currency_code_usd' => 'USD',
                'merchant_customer_id_prefix' => env('MERCHANT_CUSTOMER_ID_PREFIX', '')
            ],
        ],
    ],
];
