<?php

use App\Enums\Orders\Parts\ShippingMethod;

return [
    'min_cost_for_free_delivery' => env('MIN_COST_FOR_FREE_DELIVERY', 99),
    'methods' => [
        'enable_test_data' => env('SHIPPING_METHOD_ENABLED_TEST_DATA', false),
        'test_data' => [
            ShippingMethod::UPS_Standard() => [
                'name' => ShippingMethod::UPS_Standard(),
                'cost' => 0,
                'terms' => '2 to 4 business days',
            ],
            ShippingMethod::UPS_Next_Day_Air_Saver() => [
                'name' => ShippingMethod::UPS_Next_Day_Air_Saver(),
                'cost' => 27.05,
                'terms' => '2 business day',
            ],
            ShippingMethod::UPS_Next_Day_Air() => [
                'name' => ShippingMethod::UPS_Next_Day_Air(),
                'cost' => 63.85,
                'terms' => '1 business day',
            ],
            ShippingMethod::FedEx_Ground() => [
                'name' => ShippingMethod::FedEx_Ground(),
                'cost' => 22.33,
                'terms' => '1 business day',
            ],
            ShippingMethod::FedEx_Express_Saver() => [
                'name' =>  ShippingMethod::FedEx_Express_Saver(),
                'cost' => 29.21,
                'terms' => '1 business day',
            ],
            ShippingMethod::Pickup() => [
                'name' => ShippingMethod::Pickup(),
                'cost' => 0,
                'terms' => '1 business day',
            ]
        ]
    ]
];
