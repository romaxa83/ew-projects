<?php

use Wezom\Core\Enums\TranslationSideEnum;

return [
    TranslationSideEnum::ADMIN => [
        'admin_fail_reasons_by_myself' => 'You can`t delete yourself.',
        'forgot_password' => [
            'greeting' => 'Hello, :name!',
            'subject' => 'Password reset',
            'line_1' => 'There was a request to change your password!',
            'line_2' => 'If you did not make this request then please ignore this email.',
            'line_3' => 'Otherwise, please click this link to change your password:
        <a href=":link" style="color: red">Link</a>',
            'action' => 'Set password',
        ],

        'email_verification' => [
            'greeting' => 'Hello :name',
            'subject' => 'Email verification',
            'line' => 'To confirm the new email follow the link',
            'action' => 'Confirm',
        ],

        'set_password' => [
            'greeting' => 'Hello, :name!',
            'subject' => 'Create password',
            'line_1' => 'Your account was created! Please set a password.',
            'line_2' => 'If you did not make need account then please ignore this email.',
            'line_3' => 'Otherwise, please click this link to set your password:
        <a href=":link" style="color: red">Link</a>',
            'action' => 'Set password',

        ],
    ],
];
