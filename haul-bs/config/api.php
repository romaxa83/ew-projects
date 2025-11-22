<?php

return [
    'api_version' => '1.0',
    'api_version_allowed' => [
        '1.0'
    ],
    'api_version_deprecated' => [],
    'webhook' => [
        'token' => env('WEBHOOK_AUTH_TOKEN', 'local')
    ],
    'e_comm' => [
        'token' => env('E_COMM_AUTH_TOKEN', 'local')
    ]
];
