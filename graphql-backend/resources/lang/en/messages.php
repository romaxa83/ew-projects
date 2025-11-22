<?php

return [
    'response' => [],

    'forgot_password' => [
        'greeting' => 'Hello, :name!',
        'subject' => 'Password reset',
        'line_1' => 'There was a request to change your password!',
        'line_2' => 'If you did not make this request then please ignore this email.',
        'line_3' => 'Otherwise, please click this link to change your password: <a href=":link" style="color: red">Link</a>',
    ],

    'reset_password' => [
        'greeting' => 'Hello :name',
        'subject' => 'New password',
        'line_1' => 'Your new password: <strong>:password</strong>',
        'line_2' => 'Use the specified password to log into your account.',
        'line_3' => 'After entering your personal account, we strongly recommend that you change the password to a more understandable and reliable one.',
    ],

    'roles' => [
        'set-as-default-for-owner' => 'The role is set as default.',
        'cant-be-toggled' => 'Cant be toggled. Set for other role.',
    ],

    'user' => [
        'email-is-not-verified' => 'Your email is not verified.',
    ],

    'company' => [
        'title' => 'Company',
    ],

    'admin' => [
        'title' => 'Admin',
        'actions' => [
            'delete' => [
                'fail' => [
                    'reasons' => [
                        'by-myself' => 'You can`t delete yourself.'
                    ],
                ],
                'success' => [
                    'one-entity' => 'Deleting admin was successful.',
                    'many-entity' => 'Deleting admins was successful.',
                ],
            ],
        ],
    ],
];
