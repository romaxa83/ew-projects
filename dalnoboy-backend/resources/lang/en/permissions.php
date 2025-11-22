<?php

$list = 'List';
$create = 'Create';
$update = 'Update';
$delete = 'Delete';
$show = 'Show';

return [
    'admin' => [
        'group' => 'Admins',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
            'login_as_user' => 'Login as user'
        ],
    ],
    'user' => [
        'group' => 'Users',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],
    'role' => [
        'group' => 'Roles',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],
    'company' => [
        'group' => 'Companies',
        'grants' => [
            'list' => 'List',
            'update' => 'Update',
        ],
    ],

    'ip-access' => [
        'group' => 'Ip Access',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],

    'branch' => [
        'group' => 'Branches',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],

    'region' => [
        'group' => 'Regions',
        'grants' => [
            'show' => $show
        ]
    ],

    'manager' => [
        'group' => 'Managers',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'client' => [
        'group' => 'Clients',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'driver' => [
        'group' => 'Drivers',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'vehicle' => [
        'group' => 'Vehicles',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
        'schema' => [
            'group' => 'Vehicle schemas',
            'grants' => [
                'show' => $show,
                'create' => $create,
                'update' => $update,
                'delete' => $delete,
            ],
        ],
    ],

    'inspection' => [
        'group' => 'Inspections',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],
];
