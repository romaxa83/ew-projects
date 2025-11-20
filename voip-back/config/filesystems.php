<?php

return [
    'default' => env('FILESYSTEM_DRIVER', 'local'),

    'audio' => env('AUDIO_DISK', 'audio'),

    'audio_storage_driver' => env('AUDIO_STORAGE_DRIVER', 'public'),

    'documents' => env('DOCUMENTS_DISK', 'documents'),

    'advertisement_photos' => env('ADVERTISEMENT_PHOTOS_DISK', 'advertisement_photos'),

    'logos' => env('LOGOS_DISK', 'logos'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST', 'http::localhost'),
            'username' => env('FTP_USERNAME', 'username'),
            'password' => env('FTP_PASSWORD', 'password'),

            // Optional FTP settings...
             'port' => env('FTP_PORT', 21),
             'root' => env('FTP_ROOT', '/'),
             'passive' => true,
//             'passive' => false,
             'ignorePassiveAddress' => true,
            // 'ssl' => true,
            // 'timeout' => 30,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],

        'documents' => [
            'driver' => 'local',
            'root' => storage_path('app/public/documents'),
            'url' => env('APP_URL') . '/storage/documents',
            'visibility' => 'public',
        ],

        'advertisement_photos' => [
            'driver' => 'local',
            'root' => storage_path('app/public/advertisement_photos'),
            'url' => env('APP_URL') . '/storage/advertisement_photos',
            'visibility' => 'public',
        ],

        'logos' => [
            'driver' => 'local',
            'root' => storage_path('app/public/logos'),
            'url' => env('APP_URL') . '/storage/logos',
            'visibility' => 'public',
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
