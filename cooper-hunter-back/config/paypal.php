<?php

return [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    'mode' => env('PAYPAL_MODE'),

    'setting' => [
        'application_context' => [
            'landing_page' => 'BILLING',
            'user_action' => 'PAY_NOW',
            'payment_method' => [
                'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
            ],
        ],
        'currency_code' => 'USD',
        'country_code' => 'US',
        'checkout_lifetime' => 3, //In hours
    ],

    'urls' => [
        'api' => [
            'sandbox' => 'https://api-m.sandbox.paypal.com/',
            'live' => 'https://api-m.paypal.com/',
        ],
        'webhook' => 'webhook.paypal',
        'web' => [
            'return' => env('FRONTEND_URL') . '/account/success-pay?order_id=:id',
            'cancel' => env('FRONTEND_URL') . '/account/my-orders',
        ],
        'android' => [
            'return' => 'https://127.0.0.1/order/:id/mobilePaymentApprove',
            'cancel' => 'https://127.0.0.1/order/:id/mobilePaymentReturn',
        ],
        'ios' => [
            'return' => 'https://127.0.0.1/order/:id/mobilePaymentApprove',
            'cancel' => 'https://127.0.0.1/order/:id/mobilePaymentReturn',
        ],
        'methods' => [
            'auth' => 'v1/oauth2/token',
            'checkout' => 'v2/checkout/orders',
            'checkout_show' => 'v2/checkout/orders/:id',
            'capture_payment' => 'v2/checkout/orders/:id/capture',
            'webhook' => 'v1/notifications/webhooks',
            'webhook_update' => 'v1/notifications/webhooks/:id',
            'refund' => 'v2/payments/captures/:capture_id/refund'
        ]
    ]
];
