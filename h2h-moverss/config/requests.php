<?php

return [
    'ringostat' => [
        'host' => env('RINGOSTAT_API_HOST'),
        'paths' => [
            'json-rpc' => 'api/json-rpc'
        ]
    ],
    'vapi' => [
        'url' => env('VAPI_BASE_URL'),
        'api_key' => env('VAPI_API_KEY'),
        'paths' => [
            'assistant_list' => 'assistant',
            'call' => 'call/{id}'
        ]
    ]
];

