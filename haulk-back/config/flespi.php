<?php

return [
    'host' => env('FLESPI_HOST'),
    'token' => env('FLESPI_TOKEN'),
    'settings' => [
        'timeout' => env('FLESPI_REQUEST_TIMEOUT', 20),
        'connection_timeout' => env('FLESPI_REQUEST_CONNECTION_TIMEOUT', 20)
    ],
    'patches' => [
        'all_devices' => '/gw/devices/all',
        'device_block' => env('FLESPI_URI_DEVICE_BLOCK', '/gw/devices/{device_id}/block')
    ],
    'webhook' => [
        'auth_token' => env('FLESPI_WEBHOOK_AUTH_TOKEN')
    ]
];
