<?php
declare(strict_types=1);

return [
    'hosts' => [
        env('ELASTICSEARCH_SCHEME') . '://' . env('ELASTICSEARCH_HOST') . ':' . env('ELASTICSEARCH_PORT')
    ],
    'basicAuthentication' => [
        env('ELASTICSEARCH_USER'),
        env('ELASTICSEARCH_PASS'),
    ]
];
