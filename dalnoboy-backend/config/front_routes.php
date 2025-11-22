<?php

$frontUrl = env('FRONTEND_URL', 'https://admin.dalnoboy.wezomteam.in.ua');

return [
    'home' => $frontUrl,
    'thank-you-page' => $frontUrl . '/login',
    'set-password-page' => $frontUrl . '/set-password',
];
