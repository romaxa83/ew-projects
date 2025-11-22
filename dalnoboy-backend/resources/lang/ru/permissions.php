<?php

$create = 'Создать';
$update = 'Изменить';
$delete = 'Удалить';
$show = 'Показать';

return [
    'admin' => [
        'group' => 'Администраторы',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
            'login_as_user' => 'Войти как пользователь'
        ],
    ],
    'user' => [
        'group' => 'Пользователи',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],
    'role' => [
        'group' => 'Роли',
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
        'group' => 'Филиалы',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
    ],

    'region' => [
        'group' => 'Регионы',
        'grants' => [
            'show' => $show
        ]
    ],

    'manager' => [
        'group' => 'Менеджеры',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'client' => [
        'group' => 'Клиенты',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'driver' => [
        'group' => 'Водители',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],

    'vehicle' => [
        'group' => 'Транспортные средства',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ],
        'schema' => [
            'group' => 'Схемы ТС',
            'grants' => [
                'show' => $show,
                'create' => $create,
                'update' => $update,
                'delete' => $delete,
            ],
        ],
    ],

    'inspection' => [
        'group' => 'Инспекции',
        'grants' => [
            'show' => $show,
            'create' => $create,
            'update' => $update,
            'delete' => $delete,
        ]
    ],
];
