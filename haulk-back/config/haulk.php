<?php

return [
    'id' => 1,

    'first_name' => env('USER_NAME'),
    'last_name' => env('USER_NAME'),
    'email' => env('USER_EMAIL'),
    'password' => env('USER_PASSWORD'),

    'usdot' => '2943611',
    'mc_number' => '995965',
    'name' => 'GIG LOGISTICS INC',
    'address' => '320 W. 9 Mile Rd. Suite B, Ferndale, MI 48220',
    'city' => 'Ferndale',
    'state_id' => 10,
    'zip' => '48220',
    'timezone' => 'America/Los_Angeles',
    'phone' => '9204686975',
    'phone_name' => null,
    'phones' => [
        [
            'number' => '9204686975',
        ]
    ],
    'fax' => null,
    'website' => 'http://www.company.com',

    'billing_phone' => '9204686975',
    'billing_phone_name' => null,
    'billing_phone_extension' => null,
    'billing_phones' => [
        [
            'number' => '9204686975',
            'extension' => null,
        ],
    ],
    'billing_email' => null,
    'billing_payment_details' => null,
    'billing_terms' => null,

    'insurance_expiration_date' => null,
    'insurance_cargo_limit' => null,
    'insurance_deductible' => null,
    'insurance_agent_name' => null,
    'insurance_agent_phone' => null,
];
