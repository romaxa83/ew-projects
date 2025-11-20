<?php

return [
    'all-rights-reserved' => 'All right reserved.',
    'regards' => 'Regards',
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

    'unlimited-count' => 'Unlimited count',

    'roles' => [
        'set-as-default-for-owner' => 'The role is set as default.',
        'cant-be-toggled' => 'Cant be toggled. Set for other role.',
    ],

    'user' => [
        'email-is-not-verified' => 'Your email is not verified.',
        'account-has-been-banned' => 'Your account has been banned.',
        'do-not-have-any-company' => 'You do not work in any company.',
        'must-be-owner' => 'Only company owner can perform this action.',
        'must-be-in-same-company' => 'You can perform this action only with users from your company.',
        'registration-link-has-expired' => 'Sorry, but your invitation has expired. PLease contact with your company owner',
        'email-verification-link-has-expired' => 'Sorry, but your link has expired. PLease contact with your company owner',
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

    'reports' => [
        'file' => [
            'id' => 'Report ID',
            'report_id' => 'Report ID',
            'department_id' => 'Department',
            'employee_id' => 'Employee',
            'date_from' => 'Date from',
            'date_to' => 'Date to',
            'pause_at' => 'Pause start',
            'unpause_at' => 'End of pause',
            'duration' => 'Duration',
            'search' => 'Search',

            'name' => 'Name',
            'sip' => 'Sip number',
            'department' => 'Department',
            'total_calls' => 'Total of calls',
            'total_answered' => 'Answered of calls',
            'total_dropped' => 'Dropped of calls',
            'total_transfer' => 'Transfer of calls',
            'wait' => 'Waiting',
            'total_time' => 'Total time',
            'pause' => 'Number of pause',
            'total_pause_time' => 'Pause time',
            'date' => 'Date',
            'number' => 'Number',
            'status' => 'Status',
        ]
    ]
];
