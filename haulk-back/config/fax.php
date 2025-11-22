<?php

return [
    'contacts' => [
        'from' => env('FAX_FROM'),
    ],

    'driver' => env('FAX_DRIVER', isTesting() ? 'fake' : 'log'),

    'queue' => [
        'tries_after' => [
            'status_in_queue' => env('FAX_QUEUE_TRIES_AFTER_QUEUE_STATUS', 10),

            'status_fail' => env('FAX_QUEUE_TRIES_AFTER_FAIL_STATUS', 3),
        ],

        /*
         * in seconds
         */
        'hold_after' => [
            'status_in_queue' => env('FAX_QUEUE_HOLD_IF_STATUS_QUEUE', 300),

            'status_fail' => env('FAX_QUEUE_HOLD_IF_STATUS_FAIL', 180),
        ],
    ],

    /**
     * @see App\Services\Fax\Drivers\FaxDriver
     */
    'drivers' => [
        'clicksend' => App\Services\Fax\Drivers\ClickSend\ClickSendFaxDriver::class,
        'twilio' => App\Services\Fax\Drivers\Twilio\TwilioFaxDriver::class,
        'fake' => App\Services\Fax\Drivers\Fake\FakeFaxDriver::class,
        'log' => App\Services\Fax\Drivers\Log\LogFaxDriver::class,
    ],
];
