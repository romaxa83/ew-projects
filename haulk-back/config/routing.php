<?php

$allowedIps = parseIpAddresses(env('RATE_LIMITING_ALLOWED_IPS', ''));

return [
    'api_version' => '1',
    'api_version_allowed' => [
        '0.1',
        '1',
        '2'
    ],
    'api_version_deprecated' => [
        '0.1',
    ],
    'api_deprecation_message' => '',
    'rate_limiting_allowed_ips' => $allowedIps,
];
