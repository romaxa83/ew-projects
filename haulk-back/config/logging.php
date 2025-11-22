<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['database'],
            'ignore_exceptions' => false,
        ],

        'database' => [
            'driver' => 'custom',
            'via' => danielme85\LaravelLogToDB\LogToDbHandler::class,
            'level' => env('APP_LOG_LEVEL', 'error'), //Выше error нельзя ставить. Будут зацикливания
            'connection' => env('LOG_DB_CONNECTION', 'default'),
            'collection' => env('LOG_DB_COLLECTION', 'log'),
            'detailed' => env('LOG_DB_DETAILED', false),
            'queue' => env('LOG_DB_QUEUE', true),
            'queue_name' => env('LOG_DB_QUEUE_NAME', 'logs'),
            'queue_connection' => env('LOG_DB_QUEUE_CONNECTION', 'sync'),
            'max_records' => env('LOG_DB_MAX_COUNT', false),
            'max_hours' => env('LOG_DB_MAX_HOURS', false),
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'eyes' => [
            'enable' => env('ENABLE_LOGGER_INFO', false),
            'driver' => 'single',
            'path' => storage_path('logs/info-laravel.log'),
        ],
        'flespi' => [
            'enable' => env('ENABLE_LOGGER_FLESPI', false),
            'driver' => 'single',
            'path' => storage_path('logs/flespi.log'),
        ],
    ],

];
