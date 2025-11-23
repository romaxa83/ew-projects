<?php

use WezomCms\Core\Enums\TranslationSide;

return [
    TranslationSide::SITE => [
        'auth' => [
            'Reset Password Notification' => 'Reset Password Notification',
            'Reset password receive text1' => 'You are receiving this email because we received a password reset request for your account.',
            'If you did not request a password reset no further action is required' => 'If you did not request a password reset, no further action is required.',
            'Please click the button below to verify your email address' => 'Please click the button below to verify your email address.',
            'If you did not create an account no further action is required' => 'If you did not create an account, no further action is required.',
            'A fresh verification link has been sent to your email address' => 'A fresh verification link has been sent to your email address.',
            'You are successfully logged in' => 'You are successfully logged in.',
            'Authorisation Error Please try again' => 'Authorisation Error. Please try again',
            'User is deactivated Please contact the site administration' => 'User is deactivated. Please contact the site administration.',
            'A fresh verification code has been sent to your phone' => 'A fresh verification code has been sent to your phone.',
            'An error occurred while sending a message' => 'An error occurred while sending a message. Please contact the site administration.',
            'Thank you for registration Your password is: :password' => 'Thank you for registration. Your password is: :password',
            'failed' => 'These credentials do not match our records.',
            'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
            'passwords' => [
                'password' => 'Passwords must be at least six characters and match the confirmation.',
                'reset' => 'Your password has been reset!',
                'sent' => 'We have e-mailed your password reset link!',
                'token' => 'This password reset token is invalid.',
                'user' => "We can't find a user with that e-mail address.",
            ],
            'code' => [
                'passwords' => [
                    'password' => 'Passwords must be at least six characters and match the confirmation.',
                    'reset' => 'Your password has been reset!',
                    'sent' => 'We have e-mailed your password reset link!',
                    'token' => 'This password reset token is invalid.',
                    'user' => "We can't find a user with that e-mail address.",
                ],
            ],
        ],
        'cabinet' => [
            'Data successfully updated' => 'Your data has been successfully updated.',
        ],
    ],
];
