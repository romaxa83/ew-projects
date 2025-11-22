<?php

return [
    'base_url' => env('ONEC_BASE_URL', 'http://localhost'),
    'login' => env('ONEC_LOGIN', 'login'),
    'password' => env('ONEC_PASSWORD', 'password'),

    'base_url_suffix' => env('ONEC_BASE_URL_SUFFIX', 'developer'),

    'timeout' => 20,
    'connection_timeout' => 20,
    'set_guid_fot_test' => env('SET_GUID', false),
];
