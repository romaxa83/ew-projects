<?php

return [
    'version' => '7.0.0',
    'vendor' => [
        'name' => 'Wezom',
        'link' => 'https://wezom.com.ua',
    ],
    'app_bootstrap_path' => 'bootstrap/app.php',
    'middleware_redirect_path' => 'app/Http/Middleware/RedirectIfAuthenticated.php',
    'middleware_authenticate' => 'app/Http/Middleware/Authenticate.php',
    'middleware_csrf_token' => 'app/Http/Middleware/VerifyCsrfToken.php',
    'route_service_provider_path' => 'app/Providers/RouteServiceProvider.php',
    'clone_form_buttons_to_header' => true,
    'admin_limit' => 10,
    'map' => [
        'coordinates' => [
            'lat' => 46.9648674,
            'lng' => 31.973737,
        ],
        'height' => 400,
    ],
    'pagination' => [
        'default' => 'cms-core::admin.partials.pagination.bootstrap-4',
        'simple' => 'cms-core::admin.partials.pagination.simple-bootstrap-4',
    ],
    'og_image' => 'images/core/og-image.png',
    'protection' => [
        'crawler' => true,
        'spam' => [
            'email' => true,
            'username' => false,
            'ip' => true,
        ],
    ],
    'form_throttle' => [
        'max_attempts' => 1,
        'decay_seconds' => 10,
    ],
    'notification' => [
        'default_toast_position' => 'top-end',
    ],
    'logo' => [
        'wide' => 'images/logos/wide.png',
        'small' => 'images/logos/small.png',
        'micro_data' => 'images/core/wide.png',
    ],
    'rules' => [
        'phone' => [
            'pattern' => '/^\+380\d{9}$/',
            'format_message' => '+380XXXXXXXXX',
        ],
        'phone_mask' => [
            'mask' => '+38 (999) 999 99 99',
            'pattern' => '/^\+38\s\(0\d{2}\)\s\d{3}\s\d{2}\s\d{2}$/',
            'format_message' => '+38 (0XX) XXX XX XX',
        ],
        'phone_or_phone_mask' => [
            'pattern' => '/^(\+38\s\(0\d{2}\)\s\d{3}\s\d{2}\s\d{2})|(\+380\d{9})$/',
            'format_message' => '+38 (0XX) XXX XX XX :or +380XXXXXXXXX',
        ],
    ],
];
