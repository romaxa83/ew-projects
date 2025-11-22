<?php

return [
    'admin' => [
        'group' => 'Admins',
        'grants' => [
            'list' => 'List',
            'show' => 'Show',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
        ],
    ],

    'user' => [
        'group' => 'Users',
        'grants' => [
            'list' => 'List',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
        ],
    ],

    'role' => [
        'group' => 'Roles',
        'grants' => [
            'list' => 'List',
            'show' => 'Show',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
        ],
    ],

    'company-registration' => [
        'group' => 'Company Registration Requests',
        'grants' => [
            'list' => 'List',
            'show' => 'Show',
            'approve' => 'Approve',
            'decline' => 'Decline',
        ],
    ],

    'company' => [
        'group' => 'Companies',
        'grants' => [
            'list' => 'List',
            'show' => 'Show',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'status' => 'Status',
            'gpd_subscriptions' => 'GPS subscription',
        ],
    ],

    'support-requests' => [
        'group' => 'Support Requests',
        'grants' => [
            'list' => 'List',
            'show' => 'Show',
            'update' => 'Update',
            'change-manager' => 'Change manager',
        ],
    ],

    'gps-device' => [
        'group' => 'GPS Device',
        'grants' => [
            'list' => 'List',
            'create' => 'Create',
            'update' => 'Update',
        ],
    ],

    'gps-device-request' => [
        'group' => 'GPS Device request',
        'grants' => [
            'list' => 'List',
            'update' => 'Update',
        ],
    ],

];
