<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::SITE => [
        'custom' => [
            'password' => [
                'rule' => 'The password must contain from 8 to 30 characters, at least one digit, and one Latin letter.',
                'confirmation' => 'The passwords do not match',
            ],

            'email' => [
                'must_be_email' => 'The email field must be a valid email address.',
                'invalid' => 'Invalid email address.',
                'already_registered' => 'User with this email is already registered.',
            ],

            'credentials' => [
                'invalid' => 'Invalid credentials.',
            ],
        ],

        'attributes' => [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Password confirmation',
        ],
    ],
];
