<?php

return [
    'vpic' => [
        'url' => 'https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/%s?format=json',
        'connect_timeout' => env('VPIC_CONNECT_TIMEOUT', 10),
        'request_timeout' => env('VPIC_REQUEST_TIMEOUT', 15),
    ]
];
