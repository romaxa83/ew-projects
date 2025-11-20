<?php

$frontUrl = trim(env('FRONTEND_URL', 'https://front.alts.wezomteam.in.ua'), '/');

return [
    'home' => $frontUrl,
    'create-password-via-invitation' => $frontUrl . '/create-password',
    'verify-mail' => $frontUrl . '/verify',
    'forgot-password' => $frontUrl . '/forgot-password',
];
