<?php

use Wezom\Core\Enums\Formats\DatetimeEnum;

return [
    'admin_url' => env('BACKOFFICE_URL', 'http://localhost'),
    'front_url' => env('FRONTEND_URL', 'http://localhost'),
    'providers' => [
        Spatie\Permission\PermissionServiceProvider::class,
    ],
    'date_format' => DatetimeEnum::DATE->value,
    'datetime_format' => DatetimeEnum::DEFAULT_FORMAT->value,
    'default_pagination_count' => 10,
];
