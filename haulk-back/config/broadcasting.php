<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
                'host' => env('PUSHER_HOST', '127.0.0.1'),
                'port' => env('PUSHER_PORT', 6001),
                'scheme' => 'http'
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

    'channels' => [
        App\Broadcasting\Channels\Alerts\CompanyAlertsChannel::class,
        App\Broadcasting\Channels\Alerts\UserAlertsChannel::class,
        App\Broadcasting\Channels\OfferChannel::class,
        App\Broadcasting\Channels\OrderChannel::class,
        App\Broadcasting\Channels\SubscriptionChannel::class,
        App\Broadcasting\Channels\CarrierChannel::class,
        App\Broadcasting\Channels\NewsChannel::class,
        App\Broadcasting\Channels\ContactChannel::class,
        App\Broadcasting\Channels\UserChannel::class,
        App\Broadcasting\Channels\LibraryChannel::class,
        App\Broadcasting\Channels\PayrollChannel::class,
        App\Broadcasting\Channels\DriverTripReportChannel::class,
        App\Broadcasting\Channels\Support\Crm\SupportChannel::class,
        App\Broadcasting\Channels\Support\Crm\SupportUserChannel::class,
        App\Broadcasting\Channels\GPS\Device\Request\DeviceRequestChannel::class,
        App\Broadcasting\Channels\GPS\Device\DeviceChannel::class,
        App\Broadcasting\Channels\GPS\Device\Subscription\DeviceSubscriptionChannel::class,

        App\Broadcasting\Channels\GPS\Alerts\GpsAlertChannel::class,
        App\Broadcasting\Channels\Fueling\FuelingHistory\FuelingHistoryChannel::class
    ],

    'admin_channels' => [
        App\Broadcasting\Channels\Support\Backoffice\SupportAdminChannel::class,
        App\Broadcasting\Channels\Support\Backoffice\SupportChannel::class,
    ]
];
