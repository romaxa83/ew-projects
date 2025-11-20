<?php

return [
    'default' => 'Oops, something went wrong!',
    'email_already_verified' => 'Email already verified!',
    'value_must_be_email' => 'Value must be a valid email!',
    'account_not_active' => 'This account is not active',

    'roles' => [
        'cant_delete_role' => 'This role cannot be removed',
        'cant_delete_role_attach_user' => 'You can\'t delete a role that has a user attached to it'
    ],
    'admin' => [
        'cant_create_super_admin' => 'You can\'t create admin with role "super admin"',
        'cant_change_role_as_super_admin' => 'super admin can\'t change role',
        'cant_action_on_super_admin' => 'This action cannot be performed on "super admin"',
        'not_access' => 'You do not have access to this action',
    ],
    'sip' => [
        'cant_delete_exist_employee' => 'You can\'t remove a sip, first unbind an employee from it'
    ],
    'employee' => [
        'has_not_sip' => 'The employee does not have a sip',
        'can\'t_this_action' => 'You cannot take this action'
    ],
    'department' => [
        'cant_delete_exist_employee' => 'You can\'t remove a employee, first unbind an employee from it'
    ],
    'kamailio' => [
        'cant_delete_subscriber' => 'Can\'t delete subscriber from kamailio'
    ],
    'asterisk' => [
        'queue' => [
            'cant_delete' => 'Can\'t delete queue from asterisk'
        ],
        'queue_member' => [
            'cant_delete' => 'Can\'t delete queue member from asterisk'
        ]
    ],
    'music' => [
        'hold' => 'The record is in a hold state, no actions can be taken on it, wait until it comes out of this state'
    ]
];
