<?php

return [
    'plans' => [
        'haulk-exclusive' => [
            'id' => 1,
            'title' => 'Haulk Exclusive',
            'slug' => 'haulk-exclusive',
            'price_per_driver' => 0,
            'duration' => env('EXCLUSIVE_PLAN_DURATION', '1 month'),
            'is_trial' => false,
        ],
        'trial' => [
            'id' => 2,
            'title' => 'Free Trial',
            'slug' => 'trial',
            'price_per_driver' => 0,
            'duration' => env('TRIAL_PLAN_DURATION', '1 month'),
            'is_trial' => true,
        ],
        'regular' => [
            'id' => 3,
            'title' => 'Regular',
            'slug' => 'regular',
            'price_per_driver' => 25,
            'duration' => env('REGULAR_PLAN_DURATION', '1 month'),
            'is_trial' => false,
        ],
    ],
];
