<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::SITE => [
        'forgot_password' => [
            'greeting' => 'Hello, :name!',
            'subject' => 'Password reset',
            'line_1' => 'You have requested to recover your password',
            'line_2' => 'To set a new password follow the link below:',
            'line_3' => 'If you have not made a password recovery request, please skip this email.',
            'action' => 'Reset Password',
        ],

        'email_verification' => [
            'greeting' => 'Hello, :name!',
            'subject' => 'Email verification',
            'line_1' => 'Thank you for registration! Use the button below to confirm your email.',
            'action' => 'Confirm email',
        ],
    ],
];
