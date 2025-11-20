<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::ADMIN => [
        'currency_symbol' => 'uah',
        'auth' => [
            'Reset password receive text1' => 'You are receiving this email because we received a password reset request for your account.',
            'For security reasons your request has been canceled Please try again later' => 'For security reasons, your request has been canceled. Please try again later.',
            'failed' => 'These credentials do not match our records.',
            'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
            'passwords' => [
                'password' => 'Passwords must be at least six characters and match the confirmation.',
                'reset' => 'Your password has been reset!',
                'sent' => 'We have e-mailed your password reset link!',
                'token' => 'This password reset token is invalid.',
                'user' => "We can't find a user with that e-mail address.",
            ],
        ],
        'layout' => [
            'All rights reserved' => '© :year All rights reserved.',
            'Developed by' => 'Developed by',
            'Yes, delete it' => 'Yes, delete it!',
            'The model must have a method getMainColumn' => 'The model must have a method "getMainColumn"',
            'Invalid phone value' => 'Invalid phone value: ":input". Example: +3809333939',
            'pagination' => [
                'previous' => '&laquo; Previous',
                'next' => 'Next &raquo;',
            ],
            'The attribute field is required when' => 'The :attribute field is required when :other',
            'The attribute field is required' => 'The :attribute field is required',
        ],
        'menu' => [
            'Nothing found' => 'Nothing found',
            'Search' => 'Search',
        ],
    ],
    TranslationSide::SITE => [
        'currency_symbol' => 'uah',
    ],
];
