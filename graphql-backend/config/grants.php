<?php

use App\Models\Users\User;
use App\Permissions\Companies\CompanyUpdatePermission;
use App\Permissions\Employees\EmployeeCreatePermission;
use App\Permissions\Employees\EmployeeDeletePermission;
use App\Permissions\Employees\EmployeeListPermission;
use App\Permissions\Employees\EmployeePermissionsGroup;
use App\Permissions\Employees\EmployeeUpdatePermission;
use App\Permissions\Roles\RoleListPermission;
use App\Permissions\Roles\RolePermissionsGroup;
use App\Permissions\Users\UserCreatePermission;
use App\Permissions\Users\UserDeletePermission;
use App\Permissions\Users\UserListPermission;
use App\Permissions\Users\UserUpdatePermission;
use Core\Services\Permissions\Filters\UserEmailVerifiedPermissionFilter;

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

                App\Permissions\Companies\CompanyPermissionsGroup::class => [
                    App\Permissions\Companies\CompanyAdminListPermission::class,
                    CompanyUpdatePermission::class,
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

                App\Permissions\Users\UserPermissionsGroup::class => [
                    UserListPermission::class,
                    UserCreatePermission::class,
                    UserUpdatePermission::class,
                    UserDeletePermission::class,
                ],
            ],
        ],

        App\Models\Users\User::GUARD => [
            'groups' => [
                App\Permissions\Companies\CompanyPermissionsGroup::class => [
                    CompanyUpdatePermission::class,
                ],

                EmployeePermissionsGroup::class => [
                    EmployeeListPermission::class,
                    EmployeeCreatePermission::class,
                    EmployeeUpdatePermission::class,
                    EmployeeDeletePermission::class,
                ],

                RolePermissionsGroup::class => [
                    RoleListPermission::class,
                ],
            ],
        ],
    ],
    'filters' => [
        UserEmailVerifiedPermissionFilter::class => [],
    ],
    'filter_enabled' => env('PERMISSION_FILTER_ENABLED', true),

    /*
     * Описывает зависимости между разрешениями
     * Например: Если пользователь может создавать других пользователей, у него должен быть доступ к списку возможных ролей
     */
    'relations' => [
        User::GUARD => [
            EmployeeCreatePermission::class => [
                RoleListPermission::class,
            ],
            EmployeeUpdatePermission::class => [
                RoleListPermission::class,
            ],
        ],
    ],
];
