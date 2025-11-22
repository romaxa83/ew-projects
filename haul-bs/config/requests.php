<?php

return [
    'base_haulk' => [
        'host' => env('BASE_HAULK_HOST'),
        'secrets' => [
            'token' => env('BASE_HAULK_TOKEN')
        ],
        'settings' => [
            'timeout' => env('BASE_HAULK_REQUEST_TIMEOUT', 20),
            'connection_timeout' => env('BASE_HAULK_CONNECTION_TIMEOUT', 20)
        ],
        'paths' => [
            'get_users' => '/api/body-shop/sync/users'
        ]
    ],
    'e_com_front' => [
        'host' => env('E_COM_FRONT_HOST'),
    ],
    // для синхронизации с e-commerce хаулка
    'e_com' => [
        'enabled' => env('E_COM_ENABLED', true),
        'host' => env('E_COM_HOST'),
        'token' => env('E_COM_TOKEN'),
        'settings' => [
            'timeout' => env('E_COM_REQUEST_TIMEOUT', 20),
            'connection_timeout' => env('E_COM_CONNECTION_TIMEOUT', 20)
        ],
        'paths' => [
            'brand' => [
                'create' => 'api/brands',
                'update' => 'api/brands/{id}',
                'delete' => 'api/brands/{id}'
            ],
            'category' => [
                'exists' => 'api/categories/exists',
                'create' => 'api/categories',
                'update' => 'api/categories/{id}',
                'update_images' => 'api/categories/{id}/update-images',
                'delete' => 'api/categories/{id}'
            ],
            'feature' => [
                'create' => 'api/specifications',
                'update' => 'api/specifications/{id}',
                'delete' => 'api/specifications/{id}'
            ],
            'feature_value' => [
                'create' => 'api/spec-values',
                'update' => 'api/spec-values/{id}',
                'delete' => 'api/spec-values/{id}'
            ],
            'inventory' => [
                'exists' => 'api/products/exists',
                'create' => 'api/products',
                'update' => 'api/products/{id}',
                'update_quantity' => 'api/products/{id}/update-quantity',
                'update_images' => 'api/products/{id}/update-images',
                'delete' => 'api/products/{id}'
            ],
            'customer' => [
                'set_tag_ecomm' => 'api/user-invitations',
            ],
            'customer_tax_exemption' => [
                'create' => 'api/user-create-tax-exemption/{email}',
                'accepted' => 'api/user-create-tax-exemption/{email}/accepted',
                'decline' => 'api/user-create-tax-exemption/{email}/decline',
                'delete' => 'api/user-create-tax-exemption/{email}/delete',
            ],
            'order' => [
                'parts' => [
                    'update' => 'api/order/{id}/update',
                    'delete' => 'api/order/{id}',
                    'change_status' => 'api/order/{id}/update-status',
                    'change_status_paid' => 'api/order/{id}/update-status-paid'
                ]
            ],
            'settings' => [
                'update' => 'api/order/update-settings',
            ]
        ]
    ]
];

