<?php

return [

    'api_key' => env('USDOT_KEY'),

    'url' => env('USDOT_URL', 'https://mobile.fmcsa.dot.gov/qc/services/'),

    'path' => [
        'by-number' => env('USDOT_PATH_BY_NUMBER', 'carriers/%s'),
        ''
    ],
];
