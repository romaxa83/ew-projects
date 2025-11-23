<?php

return [
    'enabled' => env('API_ENABLED', false),
    'version' => 1,
    'shared_settings' => [
        'users.social_links.telegram_link',
        'users.social_links.instagram_link',
        'users.social_links.whatsapp_link',
    ],
];
