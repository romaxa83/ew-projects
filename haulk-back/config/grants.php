<?php

return [

    'permissions_disable' => env('PERMISSION_DISABLE', false),

    App\Models\Admins\Admin::GUARD => [
        'groups' => [

            App\Permissions\Roles\RolePermissionsGroup::class => [
                App\Permissions\Roles\RoleList::class,
                App\Permissions\Roles\RoleShow::class,
                App\Permissions\Roles\RoleCreate::class,
                App\Permissions\Roles\RoleUpdate::class,
                App\Permissions\Roles\RoleDelete::class,
            ],

            App\Permissions\Admins\AdminPermissionsGroup::class => [
                App\Permissions\Admins\AdminList::class,
                App\Permissions\Admins\AdminShow::class,
                App\Permissions\Admins\AdminCreate::class,
                App\Permissions\Admins\AdminUpdate::class,
                App\Permissions\Admins\AdminDelete::class,
            ],

            App\Permissions\Users\UserPermissionsGroup::class => [
                App\Permissions\Users\UserList::class,
                App\Permissions\Users\UserCreate::class,
                App\Permissions\Users\UserUpdate::class,
                App\Permissions\Users\UserDelete::class,
            ],

            App\Permissions\Saas\Companies\CompanyPermissionsGroup::class => [
                App\Permissions\Saas\Companies\CompanyList::class,
                App\Permissions\Saas\Companies\CompanyShow::class,
                App\Permissions\Saas\Companies\CompanyCreate::class,
                App\Permissions\Saas\Companies\CompanyUpdate::class,
                App\Permissions\Saas\Companies\CompanyDelete::class,
                App\Permissions\Saas\Companies\CompanyStatus::class,
                App\Permissions\Saas\Companies\CompanyGpsSubscription::class,
            ],

            App\Permissions\Saas\CompanyRegistration\CompanyRegistrationPermissionsGroup::class => [
                App\Permissions\Saas\CompanyRegistration\CompanyRegistrationApprove::class,
                App\Permissions\Saas\CompanyRegistration\CompanyRegistrationDecline::class,
                App\Permissions\Saas\CompanyRegistration\CompanyRegistrationList::class,
                App\Permissions\Saas\CompanyRegistration\CompanyRegistrationShow::class,
            ],

            App\Permissions\Saas\Translations\TranslationPermissionsGroup::class => [
                App\Permissions\Saas\Translations\TranslationList::class,
                App\Permissions\Saas\Translations\TranslationShow::class,
                App\Permissions\Saas\Translations\TranslationCreate::class,
                App\Permissions\Saas\Translations\TranslationUpdate::class,
                App\Permissions\Saas\Translations\TranslationDelete::class,
            ],
            App\Permissions\Saas\Support\SupportRequestPermissionsGroup::class => [
                App\Permissions\Saas\Support\SupportRequestChangeManager::class,
                App\Permissions\Saas\Support\SupportRequestList::class,
                App\Permissions\Saas\Support\SupportRequestUpdate::class,
                App\Permissions\Saas\Support\SupportRequestShow::class
            ],

            App\Permissions\Saas\Invoices\InvoicePermissionsGroup::class => [
                App\Permissions\Saas\Invoices\InvoiceList::class,
                App\Permissions\Saas\Invoices\InvoiceShow::class,
            ],

            App\Permissions\Saas\GPS\Devices\DevicePermissionsGroup::class => [
                App\Permissions\Saas\GPS\Devices\DeviceCreate::class,
                App\Permissions\Saas\GPS\Devices\DeviceUpdate::class,
                App\Permissions\Saas\GPS\Devices\DeviceList::class,
            ],

            App\Permissions\Saas\GPS\Devices\Request\DeviceRequestPermissionGroup::class => [
                App\Permissions\Saas\GPS\Devices\Request\DeviceRequestUpdate::class,
                App\Permissions\Saas\GPS\Devices\Request\DeviceRequestList::class,
            ],

            App\Permissions\Saas\GPS\History\HistoryPermissionsGroup::class => [
                App\Permissions\Saas\GPS\History\HistoryList::class,
            ],

            App\Permissions\Saas\GPS\Alerts\AlertPermissionsGroup::class => [
                App\Permissions\Saas\GPS\Alerts\AlertList::class,
            ],

        ],
    ],

    App\Models\Users\User::GUARD => [
        'groups' => [],
    ],

];
