<?php

$frontUrl = env('FRONTOFFICE_URL', 'http://localhost');

return [
    'frontoffice' => [
        'home' => $frontUrl,
        'email-confirm' => $frontUrl . '/email-confirm',
        'password-reset' => $frontUrl . '/password-reset',
    ],
];
