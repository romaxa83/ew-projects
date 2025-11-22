<?php

return [
    'validation_messages' => [
        'odo' => [
            'too_small' => 'Current odometer readings are less than those set in the vehicle',
            'is_required' => 'The odometer reading is mandatory for the type of vehicle',
        ],
        'photos' => [
            'sign' => [
                'is_required' => 'You must upload a signature image or indicate "unable to take signature"'
            ],
            'vehicle' => [
                'is_required' => 'You must upload a vehicle photo'
            ],
            'state_number' => [
                'is_required' => 'You must upload a state number photo'
            ],
            'odo' => [
                'is_required' => 'You must upload a odometer photo'
            ],
        ],
        'tire' => [
            'ogp_bigger_ngp' => 'Tire\'s OGP can not be bigger than NGP',
            'ogp_too_big' => 'Tire\'s OGP is bigger than OGP this tire in previous inspection',
            'same_tire' => 'You can\'t use the same tires'
        ],
    ],
    'can_not_update' => 'You can not update the inspection. The inspection created more than 72 hours ago',
    'linked' => [
        'incorrect_vehicle_form' => 'In the specified inspection, the type of vehicle does not correspond',
        'main_has_trailer' => 'Main vehicle inspection already linked to another trailer inspection',
        'trailer_has_main' => 'Trailer vehicle inspection already linked to another main vehicle inspection',
        'main_has_not_trailer' => 'Main vehicle inspection does not linked to any trailer inspection',
    ],
];
