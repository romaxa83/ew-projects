<?php

use App\Permissions\Roles\RoleListPermission;
use App\Permissions\Roles\RolePermissionsGroup;

return [

    'permissions_disable' => env('PERMISSION_DISABLE', false),

    'matrix' => [
        App\Models\Admins\Admin::GUARD => [
            'groups' => [
                App\Permissions\Admins\AdminPermissionsGroup::class => [
                    App\Permissions\Admins\AdminListPermission::class,
                    App\Permissions\Admins\AdminCreatePermission::class,
                    App\Permissions\Admins\AdminUpdatePermission::class,
                    App\Permissions\Admins\AdminDeletePermission::class,
                ],

                App\Permissions\Departments\PermissionGroup::class => [
                    App\Permissions\Departments\ListPermission::class,
                    App\Permissions\Departments\UpdatePermission::class,
                    App\Permissions\Departments\CreatePermission::class,
                    App\Permissions\Departments\DeletePermission::class,
                ],

                App\Permissions\Employees\PermissionGroup::class => [
                    App\Permissions\Employees\ListPermission::class,
                    App\Permissions\Employees\UpdatePermission::class,
                    App\Permissions\Employees\CreatePermission::class,
                    App\Permissions\Employees\DeletePermission::class,
                    App\Permissions\Employees\ChangeStatusPermission::class,
                ],

                App\Permissions\Sips\PermissionGroup::class => [
                    App\Permissions\Sips\ListPermission::class,
                    App\Permissions\Sips\UpdatePermission::class,
                    App\Permissions\Sips\CreatePermission::class,
                    App\Permissions\Sips\DeletePermission::class,
                ],

                App\Permissions\Localization\TranslatePermissionGroup::class => [
                    App\Permissions\Localization\TranslateListPermission::class,
                    App\Permissions\Localization\TranslateUpdatePermission::class,
                    App\Permissions\Localization\TranslateDeletePermission::class,
                ],

                RolePermissionsGroup::class => [
                    RoleListPermission::class,
                    App\Permissions\Roles\RoleCreatePermission::class,
                    App\Permissions\Roles\RoleUpdatePermission::class,
                    App\Permissions\Roles\RoleDeletePermission::class,
                ],

                App\Permissions\Security\IpAccessPermissionsGroup::class => [
                    App\Permissions\Security\IpAccessListPermission::class,
                    App\Permissions\Security\IpAccessCreatePermission::class,
                    App\Permissions\Security\IpAccessUpdatePermission::class,
                    App\Permissions\Security\IpAccessDeletePermission::class,
                ],

                App\Permissions\Calls\History\PermissionGroup::class => [
                    App\Permissions\Calls\History\ListPermission::class,
                ],

                App\Permissions\Calls\Queue\PermissionGroup::class => [
                    App\Permissions\Calls\Queue\ListPermission::class,
                    App\Permissions\Calls\Queue\UpdatePermission::class,
                    App\Permissions\Calls\Queue\TransferPermission::class,
                ],

                App\Permissions\Reports\PermissionGroup::class => [
                    App\Permissions\Reports\ListPermission::class,
                    App\Permissions\Reports\DownloadPermission::class,
                ],

                App\Permissions\Schedules\PermissionGroup::class => [
                    App\Permissions\Schedules\ListPermission::class,
                    App\Permissions\Schedules\UpdatePermission::class,
                ],

                App\Permissions\Musics\PermissionGroup::class => [
                    App\Permissions\Musics\ListPermission::class,
                    App\Permissions\Musics\UpdatePermission::class,
                    App\Permissions\Musics\CreatePermission::class,
                    App\Permissions\Musics\DeletePermission::class,
                    App\Permissions\Musics\UploadPermission::class,
                ],
            ],
        ],
        App\Models\Employees\Employee::GUARD => [
            'groups' => [
                App\Permissions\Departments\PermissionGroup::class => [
                    App\Permissions\Departments\ListPermission::class,
                ],

                App\Permissions\Employees\PermissionGroup::class => [
                    App\Permissions\Employees\ListPermission::class,
                    App\Permissions\Employees\ChangeStatusPermission::class,
                ],

                App\Permissions\Sips\PermissionGroup::class => [
                    App\Permissions\Sips\ListPermission::class,
                ],

                App\Permissions\Calls\History\PermissionGroup::class => [
                    App\Permissions\Calls\History\ListPermission::class,
                ],

                App\Permissions\Calls\Queue\PermissionGroup::class => [
                    App\Permissions\Calls\Queue\ListPermission::class,
                    App\Permissions\Calls\Queue\UpdatePermission::class,
                    App\Permissions\Calls\Queue\TransferPermission::class,
                ],

                App\Permissions\Reports\PermissionGroup::class => [
                    App\Permissions\Reports\ListPermission::class,
                    App\Permissions\Reports\DownloadPermission::class,
                ],
            ],
        ],
    ],
    'filters' => [],
    'filter_enabled' => env('PERMISSION_FILTER_ENABLED', true),

    /*
     * Описывает зависимости между разрешениями
     * Например: Если пользователь может создавать других пользователей, у него должен быть доступ к списку возможных ролей
     */
    'relations' => [
        \App\Models\Employees\Employee::GUARD => []
    ],
    'hidden' => [
        App\Models\Admins\Admin::GUARD => [
            'groups' => [
                App\Permissions\Admins\AdminPermissionsGroup::class => [
                    App\Permissions\Admins\AdminListPermission::class,
                    App\Permissions\Admins\AdminCreatePermission::class,
                    App\Permissions\Admins\AdminUpdatePermission::class,
                    App\Permissions\Admins\AdminDeletePermission::class,
                ],

                RolePermissionsGroup::class => [
                    RoleListPermission::class,
                    App\Permissions\Roles\RoleCreatePermission::class,
                    App\Permissions\Roles\RoleUpdatePermission::class,
                    App\Permissions\Roles\RoleDeletePermission::class,
                ],
            ],
        ],
    ]
];
