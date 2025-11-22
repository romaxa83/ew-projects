<?php

return [
    'device' => [
        'request_activate' => ':company_name has sent request for activation for device :imei',
        'request_deactivate' => ':company_name has sent request for deactivation for device :imei',

        'for_crm' => [
            'take_request' => 'Your request for additional devices is being processed',
            'close_request' => 'Your request for additional devices is closed. Please check the list of GPS devices to activate them',
            'deactivate' => 'Your request for deactivation of device IMEI :imei has been approved. It will be deactivated after your current billing period ends',
            'activate' => 'Your request for activation of device IMEI :imei has been approved. Now you can assign it to a vehicle',
            'cancel_subscription' => 'Your request for additional devices is closed because your GPS subscription is cancelled.',
        ]
    ],
    'gps_subscription' => [
        'cancel' => ":company_name has sent a request to cancel GPS subscription. Please check the list of devices for deactivation",
        'warning_cancel' => "Today is the last day of GPS subscription for :company_name. Please check the list of devices for deactivation",
        'change_rate' => "The new rate per GPS device is USD :rate. It comes into force from :date."
    ],
    'company_subscription' => [
        'cancel' => ':company_name has canceled Haulk subscription and GPS subscription is canceled too. Please check the list of devices to confirm deactivation.',
        'unpaid' => ':company_name has unpaid account. Here is a list of devices for deactivation'
    ],

];
