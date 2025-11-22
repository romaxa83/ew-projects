<?php

$create = 'Создать';
$update = 'Изменить';
$delete = 'Удалить';
$show = 'Показать';

return [
    'admin' => [
        'group' => 'Адміністратори',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
            'login_as_user' => 'Увійти як користувач'
        ],
    ],
    'user' => [
        'group' => 'Користувачі',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],
    'role' => [
        'group' => 'Ролі',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],

    'ip-access' => [
        'group' => 'Ip доступ',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],

    'branch' => [
        'group' => 'Філії',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],

    'region' => [
        'group' => 'Регіони',
        'grants' => [
            'show' => $show
        ]
    ],

    'manager' => [
        'group' => 'Менеджери',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'client' => [
        'group' => 'Клієнти',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'driver' => [
        'group' => 'Водії',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'vehicle' => [
        'group' => 'Транспортні засоби',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
        'schema' => [
            'group' => 'Схеми ТЗ',
            'grants' => [
                'show' => $show,
                'create' => $create,
                'update' => $update,
                'delete' => $delete,
            ],
        ],
    ],

    'inspection' => [
        'group' => 'Інспекції',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],
];
