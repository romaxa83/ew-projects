<?php

return [
    'default_speed_limit' => env('GPS_DEFAULT_SPEED_LIMIT', 70), // mi/h
    'low_battery_level' => env('GPS_LOW_BATTERY_LEVEL', 20), // %
    'device_disconnected_time' => env('GPS_DEVICE_DISCONNECTED_TIME', 7 * 60), //sec
    'device_disconnected_time_not_speed' => env('GPS_DEVICE_DISCONNECTED_TIME_NOT_SPEED', 62), //minutes
    'long_idle_min_duration' => env('GPS_LONG_IDLE_MIN_DURATION', 600), // seconds
    'count_days_to_show_alerts' => env('GPS_COUNT_DAYS_TO_SHOW_ALERTS', 50), //days

    'device_disconnected_time_on_stop' => env('GPS_DEVICE_DISCONNECTED_TIME_ON_STOP', 20 * 60), //sec
    'device_disconnected_time_on_not_stop' => env('GPS_DEVICE_DISCONNECTED_TIME_ON_NOT_STOP', 7 * 60), //sec

    'subscription' => [
        'warning_notifications_hours' => 24 // hours
    ]
];
