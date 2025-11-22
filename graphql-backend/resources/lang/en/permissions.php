<?php

$list = 'List';
$create = 'Create';
$update = 'Update';
$delete = 'Delete';

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
    'user' => [
        'group' => 'Users',
        'grants' => $baseGrants,
    ],
    'employee' => [
        'group' => 'Employees',
        'grants' => $baseGrants,
    ],
    'role' => [
        'group' => 'Roles',
        'grants' => $baseGrants,
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
            'list' => $list,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],
];
