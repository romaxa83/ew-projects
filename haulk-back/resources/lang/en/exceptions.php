<?php

return [
    'vehicle' => [
        'truck' => [
            'not_found' => "Truck no found by :attribute - :value"
        ],
        'trailer' => [
            'not_found' => "Trailer no found by :attribute - :value"
        ],
    ],
    'gps_device' => [
        'has_attached_vehicle' => 'There is an attached vehicle',
        'cant_toggle_because_delete' => 'The device cannot be switched because it was deleted',
        'device_must_be_pending' => 'The device must be a pending request status',
        'no_activate_not_company_or_phone' => 'To activate the device, she must have a company and phone number',
        'request' => [
            'closed_for_editing' => 'Request closed for editing'
        ],
        'subscription' => [
            'not_active_subscription' => "You don't have an active subscription",
            'subscription_disabled' => "Devices cannot be manipulated because the subscription is disabled",
            'not_restore' => "When restored, the subscription must be in the status - active_till",
        ]
    ],
    'user' => [
        'driver' => [
            'not_driver' => "The transferred user is not the driver",
            'history' => [
                "driver_assigned_another_truck" => "This driver is assigned to another truck U/N - :unit_number on the selected date and time",
                "driver_assigned_another_trailer" => "This driver is assigned to another trailer U/N - :unit_number on the selected date and time"
            ]
        ]
    ],
    'company' => [
        'billing' => [
            'has_unpaid_invoice' => "Access denied company ':company_name' has an unpaid invoice",
            'not_active' => "You cannot perform this action the company ':company_name' has canceled subscription"
        ]
    ]
];

