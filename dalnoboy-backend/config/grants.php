<?php

return [

    'permissions_disable' => env('PERMISSION_DISABLE', false),

    'matrix' => [
        App\Models\Admins\Admin::GUARD => [
            'groups' => [
                App\Permissions\Admins\AdminPermissionsGroup::class => [
                    App\Permissions\Admins\AdminShowPermission::class,
                    App\Permissions\Admins\AdminCreatePermission::class,
                    App\Permissions\Admins\AdminUpdatePermission::class,
                    App\Permissions\Admins\AdminDeletePermission::class,
                ],

                App\Permissions\Localization\TranslatePermissionGroup::class => [
                    App\Permissions\Localization\TranslateShowPermission::class,
                    App\Permissions\Localization\TranslateCreatePermission::class,
                    App\Permissions\Localization\TranslateUpdatePermission::class,
                    App\Permissions\Localization\TranslateDeletePermission::class,
                ],

                App\Permissions\Roles\RolePermissionsGroup::class => [
                    App\Permissions\Roles\RoleListPermission::class,
                    App\Permissions\Roles\RoleCreatePermission::class,
                    App\Permissions\Roles\RoleUpdatePermission::class,
                    App\Permissions\Roles\RoleDeletePermission::class,
                ],

                App\Permissions\Users\UserPermissionsGroup::class => [
                    App\Permissions\Users\UserShowPermission::class,
                    App\Permissions\Users\UserCreatePermission::class,
                    App\Permissions\Users\UserUpdatePermission::class,
                    App\Permissions\Users\UserDeletePermission::class,
                ],

                App\Permissions\Branches\BranchPermissionsGroup::class => [
                    App\Permissions\Branches\BranchShowPermission::class,
                    App\Permissions\Branches\BranchCreatePermission::class,
                    App\Permissions\Branches\BranchDeletePermission::class,
                    App\Permissions\Branches\BranchUpdatePermission::class,
                ],

                App\Permissions\Managers\ManagerPermissionsGroup::class => [
                    App\Permissions\Managers\ManagerShowPermission::class,
                    App\Permissions\Managers\ManagerCreatePermission::class,
                    App\Permissions\Managers\ManagerUpdatePermission::class,
                    App\Permissions\Managers\ManagerDeletePermission::class,
                ],

                App\Permissions\Clients\ClientPermissionsGroup::class => [
                    App\Permissions\Clients\ClientShowPermission::class,
                    App\Permissions\Clients\ClientCreatePermission::class,
                    App\Permissions\Clients\ClientUpdatePermission::class,
                    App\Permissions\Clients\ClientDeletePermission::class,
                ],

                App\Permissions\Drivers\DriverPermissionsGroup::class => [
                    App\Permissions\Drivers\DriverCreatePermission::class,
                    App\Permissions\Drivers\DriverUpdatePermission::class,
                    App\Permissions\Drivers\DriverDeletePermission::class,
                    App\Permissions\Drivers\DriverShowPermission::class,
                ],

                App\Permissions\Vehicles\Schemas\VehicleSchemaPermissionsGroup::class => [
                    App\Permissions\Vehicles\Schemas\VehicleSchemaCreatePermission::class,
                    App\Permissions\Vehicles\Schemas\VehicleSchemaUpdatePermission::class,
                    App\Permissions\Vehicles\Schemas\VehicleSchemaDeletePermission::class,
                    App\Permissions\Vehicles\Schemas\VehicleSchemaShowPermission::class,
                ],

                App\Permissions\Vehicles\VehiclePermissionsGroup::class => [
                    App\Permissions\Vehicles\VehicleCreatePermission::class,
                    App\Permissions\Vehicles\VehicleUpdatePermission::class,
                    App\Permissions\Vehicles\VehicleDeletePermission::class,
                    App\Permissions\Vehicles\VehicleShowPermission::class,
                ],

                App\Permissions\Locations\RegionPermissionGroup::class => [
                    App\Permissions\Locations\RegionShowPermission::class,
                ],

                App\Permissions\Dictionaries\DictionaryPermissionsGroup::class => [
                    App\Permissions\Dictionaries\DictionaryCreatePermission::class,
                    App\Permissions\Dictionaries\DictionaryUpdatePermission::class,
                    App\Permissions\Dictionaries\DictionaryDeletePermission::class,
                    App\Permissions\Dictionaries\DictionaryShowPermission::class,
                ],

                App\Permissions\Tires\TirePermissionsGroup::class => [
                    App\Permissions\Tires\TireCreatePermission::class,
                    App\Permissions\Tires\TireUpdatePermission::class,
                    App\Permissions\Tires\TireDeletePermission::class,
                    App\Permissions\Tires\TireShowPermission::class,
                ],

                App\Permissions\Settings\SettingsPermissionsGroup::class => [
                    App\Permissions\Settings\SettingsUpdatePermission::class,
                    App\Permissions\Settings\SettingsShowPermission::class,
                ],

                App\Permissions\Inspections\InspectionPermissionsGroup::class => [
                    App\Permissions\Inspections\InspectionUpdatePermission::class,
                    App\Permissions\Inspections\InspectionShowPermission::class,
                    App\Permissions\Inspections\InspectionDeletePermission::class,
                ],
            ],
        ],

        App\Models\Users\User::GUARD => [
            'groups' => [
                App\Permissions\Localization\TranslatePermissionGroup::class => [
                    App\Permissions\Localization\TranslateShowPermission::class,
                ],

                App\Permissions\Roles\RolePermissionsGroup::class => [
                    App\Permissions\Roles\RoleListPermission::class,
                ],

                App\Permissions\Locations\RegionPermissionGroup::class => [
                    App\Permissions\Locations\RegionShowPermission::class,
                ],

                App\Permissions\Users\UserPermissionsGroup::class => [
                    App\Permissions\Users\UserShowPermission::class,
                ],

                App\Permissions\Clients\ClientPermissionsGroup::class => [
                    App\Permissions\Clients\ClientShowPermission::class,
                    App\Permissions\Clients\ClientCreatePermission::class,
                    App\Permissions\Clients\ClientUpdatePermission::class
                ],

                App\Permissions\Drivers\DriverPermissionsGroup::class => [
                    App\Permissions\Drivers\DriverCreatePermission::class,
                    App\Permissions\Drivers\DriverUpdatePermission::class,
                    App\Permissions\Drivers\DriverShowPermission::class,
                ],

                App\Permissions\Vehicles\Schemas\VehicleSchemaPermissionsGroup::class => [
                    App\Permissions\Vehicles\Schemas\VehicleSchemaShowPermission::class,
                ],

                App\Permissions\Vehicles\VehiclePermissionsGroup::class => [
                    App\Permissions\Vehicles\VehicleCreatePermission::class,
                    App\Permissions\Vehicles\VehicleUpdatePermission::class,
                    App\Permissions\Vehicles\VehicleShowPermission::class,
                ],

                App\Permissions\Dictionaries\DictionaryPermissionsGroup::class => [
                    App\Permissions\Dictionaries\DictionaryShowPermission::class,
                    App\Permissions\Dictionaries\DictionaryCreatePermission::class,
                ],

                App\Permissions\Tires\TirePermissionsGroup::class => [
                    App\Permissions\Tires\TireShowPermission::class,
                    App\Permissions\Tires\TireCreatePermission::class,
                    App\Permissions\Tires\TireUpdatePermission::class,
                ],

                App\Permissions\Settings\SettingsPermissionsGroup::class => [
                    App\Permissions\Settings\SettingsShowPermission::class,
                ],

                App\Permissions\Branches\BranchPermissionsGroup::class => [
                    App\Permissions\Branches\BranchShowPermission::class,
                ],

                App\Permissions\Inspections\InspectionPermissionsGroup::class => [
                    App\Permissions\Inspections\InspectionCreatePermission::class,
                    App\Permissions\Inspections\InspectionUpdatePermission::class,
                    App\Permissions\Inspections\InspectionShowPermission::class,
                ],
            ],
        ],
    ],
    'filters' => [
        //UserEmailVerifiedPermissionFilter::class => [],
    ],
    'filter_enabled' => env('PERMISSION_FILTER_ENABLED', true),

    /*
     * Описывает зависимости между разрешениями
     * Например: Если пользователь может создавать других пользователей, у него должен быть доступ к списку возможных ролей
     */
    'relations' => [

    ],
];
