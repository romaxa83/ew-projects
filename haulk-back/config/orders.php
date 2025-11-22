<?php

use App\Models\Orders\Order;

return [
    'invoice' => [
        'price_decimal' => env('ORDER_INVOICE_PRICE_DECIMAL', 2),
    ],

    'contacts' => [
        'max_phone_prefix_length' => env('ORDER_CONTACT_MAX_PHONE_PREFIX_LENGTH', 4),
    ],

    'inspection' => [
        'min_photo' => [
            'boat' => env('ORDER_INSPECTION_MIN_PHOTO_boat', 11),
            'motorcycle' => env('ORDER_INSPECTION_MIN_PHOTO_motorcycle', 6),
            'other' => env('ORDER_INSPECTION_MIN_PHOTO_OTHER', 15),
        ],

        'max_photo' => env('ORDER_INSPECTION_MAX_PHOTO', 100),

        'damage_labels' => [
            'S' => 'Scratched',
            'CH' => 'Chipped',
            'D' => 'Dented',
            'MS' => 'Multiple Scratches',
            'M' => 'Missing',
            'CR' => 'Cracked',
            'MD' => 'Major Damage',
            'BR' => 'Broken',
            'FT' => 'Flat Tire',
            'PC' => 'Paint Chip',
            'HD' => 'Hail Damage',
            'F' => 'Faded',
            'G' => 'Gouge',
            'RB' => 'Rubbed',
            'SF' => 'Scuffed',
            'LC' => 'Loose Contents',
            'R' => 'Rust',
            'FF' => 'Foreign Fluid',
            'O' => 'Other',
        ],
        'signature_bol_link_life' => env('ORDER_SIGNATURE_BOL_LINK_LIFE', 10800)
    ],

    'vehicles' => [
        'types' => [
            'public_dir' => env('ORDER_VEHICLE_IMAGE_TYPE_PATH', 'vehicle-schemes'),
            'extension' => env('ORDER_VEHICLE_IMAGE_TYPE_EXTENSION', 'png'),
        ],
    ],
    /*
     * All paid orders will be deleted after
     */
    'delete_after' => env('ORDERS_DELETE_AFTER', 730),

    /*
     * All deleted orders will be purged after
     */
    'purge_after' => env('ORDERS_PURGE_AFTER', 30),

    'sorting_by_calculated_status' => [
        Order::CALCULATED_STATUS_OFFER => 0,
        Order::CALCULATED_STATUS_NEW => 10,
        Order::CALCULATED_STATUS_ASSIGNED => 20,
        Order::CALCULATED_STATUS_PICKED_UP => 30,
        Order::CALCULATED_STATUS_DELIVERED => 40,
        Order::CALCULATED_STATUS_DELETED => 1000,
    ],

    'mobile' => [
        'history' => [
            'days' => env('ORDER_MOBILE_HISTORY_DAYS', 180),
        ]
    ],

    'payment' => [
        'days' => explode(',', env('PAYMENT_DAYS', '0,2,5,10,15,20,25,30')),
    ]
];
