<?php

return [
    'url' => env('SAAS_URL'),
    'system' => [
        'delete_media_batch_size' => 100,
    ],
    'company' => [
        'destroy_token_life' => 1800
    ]
];
