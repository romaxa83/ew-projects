<?php

return [
    'rdp' => [
        'account' => [
            'ou' => env('RDP_ACCOUNT_OU', 'testgp'),
            'group' => env('RDP_ACCOUNT_GROUP', 'cooperhunter'),
            'principal' => env('RDP_ACCOUNT_PRINCIPAL_SUFFIX', '@cooperhunter.loc'),

            'limit_session' => env('RDP_ACCOUNT_LIMIT_SESSION', 60 * 24),
            'idle_session' => env('RDP_ACCOUNT_IDLE_SESSION', 60 * 3),
        ],

        'credentials' => [
            /**
             * Should be a valid DateInterval string
             *
             * @link https://php.net/manual/en/dateinterval.createfromdatestring.php
             */
            'expiration_interval' => env('RPD_CREDENTIALS_EXPIRATION_INTERVAL', '1 month'),

            /**
             * Should be a valid DateInterval string
             *
             * @link https://php.net/manual/en/dateinterval.createfromdatestring.php
             */
            'make_request_until' => env('RDP_CREDENTIALS_REQUEST_UNTIL', '2 weeks'),
        ],
    ],
];