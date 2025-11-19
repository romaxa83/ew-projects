<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::ADMIN => [
        'admin_not_exists' => 'Admin not exists',
        'this_admin_has_been_deactivated' => 'This admin has been deactivated',
        'custom' => [
            'reset_password' => [
                'time' => 'Password reset timed out',
                'user' => 'User is not found',
                'code' => 'Invalid password reset link',
            ],
        ],
    ],
];
