<?php

$list = 'List';
$create = 'Create';
$update = 'Update';
$delete = 'Delete';
$upload = 'Upload';

$baseGrants = [
    'list' => $list,
    'create' => $create,
    'update' => $update,
    'delete' => $delete,
];

return [
    'admin' => [
        'group' => 'Admins',
        'grants' => $baseGrants,
    ],
    'role' => [
        'group' => 'Roles',
        'grants' => $baseGrants,
    ],
    'ip-access' => [
        'group' => 'Ip Access',
        'grants' => $baseGrants,
    ],
    'departments' => [
        'group' => 'Departments',
        'grants' => $baseGrants,
    ],
    'employees' => [
        'group' => 'Employees',
        'grants' => $baseGrants + [
            'change-status' => 'Change status'
            ],
    ],
    'musics' => [
        'group' => 'Music',
        'grants' => $baseGrants + [
            'upload' => $upload
            ],
    ],
    'sips' => [
        'group' => 'Sips',
        'grants' => $baseGrants,
    ],
    'calls' => [
        'history' => [
            'group' => 'History',
            'grants' => $baseGrants,
        ],
        'queue' => [
            'group' => 'Queue',
            'grants' => $baseGrants + [
                'transfer' => 'Transfer'
            ],
        ]
    ],
    'reports' => [
        'group' => 'Queue',
        'grants' => $baseGrants + [
                'download' => 'Download'
            ],
    ]
];
