<?php

return [
    'headers' => [
        'order_id' => 'Haulk-Track-Id',
        'email_type' => 'Haulk-Track-Type',
        'env_type' => 'Haulk-Env-Type'
    ],
    'log_token' => env('EMAIL_DELIVERY_LOG_TOKEN'),
    'env_type' => env('ENV_TYPE')
];
