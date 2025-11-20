<?php

declare(strict_types=1);

// The schemas for query and/or mutation. It expects an array of schemas to provide
// both the 'query' fields and the 'mutation' fields.
//
// You can also provide a middleware that will only apply to the given schema
//
// Example:
//
//  'schema' => 'default',
//
//  'schemas' => [
//      'default' => [
//          'query' => [
//              'users' => 'App\GraphQL\Query\UsersQuery'
//          ],
//          'mutation' => [
//
//          ]
//      ],
//      'user' => [
//          'query' => [
//              'profile' => 'App\GraphQL\Query\ProfileQuery'
//          ],
//          'mutation' => [
//
//          ],
//          'middleware' => ['auth'],
//      ],
//      'user/me' => [
//          'query' => [
//              'profile' => 'App\GraphQL\Query\MyProfileQuery'
//          ],
//          'mutation' => [
//
//          ],
//          'middleware' => ['auth'],
//      ],
//  ]

use App\GraphQL\Mutations\BackOffice\Auth\ForgotPasswordMutation;

return [
    'default' => [
        'query' => [
            App\GraphQL\Queries\Common\Localization\LanguagesQuery::class,
            App\GraphQL\Queries\Common\Localization\TranslatesSimpleQuery::class,
        ],
        'mutation' => [
            App\GraphQL\Mutations\Common\Localization\SetLanguageMutation::class,
        ],
        'subscription' => [],
        'middleware' => [],
        'method' => ['post'],
    ],
    'BackOffice' => [
        'query' => [
            // Calls History --------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Calls\History\HistoriesQuery::class,
            // ----------------------------------------------------------------------------

            // Calls Queue ----------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Calls\Queue\QueuesQuery::class,
            App\GraphQL\Queries\BackOffice\Calls\Queue\QueuesListQuery::class,
            // ----------------------------------------------------------------------------

            // Departments ----------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Departments\DepartmentsQuery::class,
            App\GraphQL\Queries\BackOffice\Departments\DepartmentsListQuery::class,
            // ----------------------------------------------------------------------------

            // Reports --------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Reports\ReportPauseItemsQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportPauseItemsAdditionalQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportPauseItemsExcelQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportItemsQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportsQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportsAdditionalQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportItemsAdditionalQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportsExcelQuery::class,
            App\GraphQL\Queries\BackOffice\Reports\ReportItemsExcelQuery::class,
            // ----------------------------------------------------------------------------

            // Sips -----------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Sips\SipsQuery::class,
            App\GraphQL\Queries\BackOffice\Sips\SipsListQuery::class,
            // ----------------------------------------------------------------------------

            // Employees-------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Employees\EmployeesQuery::class,
            App\GraphQL\Queries\BackOffice\Employees\EmployeesListQuery::class,
            // ----------------------------------------------------------------------------

            // Admins ---------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Admins\AdminsQuery::class,
            App\GraphQL\Queries\BackOffice\Admins\AdminsListQuery::class,
            // ----------------------------------------------------------------------------

            // Schedules ------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Schedules\SchedulesQuery::class,
            // ----------------------------------------------------------------------------

            // Musics ---------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Musics\MusicsQuery::class,
            App\GraphQL\Queries\BackOffice\Musics\MusicsListQuery::class,
            // ----------------------------------------------------------------------------

            // Auth -----------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Auth\Admin\AdminProfileQuery::class,
            App\GraphQL\Queries\BackOffice\Auth\Employee\EmployeeProfileQuery::class,

//            App\GraphQL\Queries\BackOffice\Auth\AuthProfileQuery::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Mutations\Common\Localization\SetLanguageMutation::class,
            App\GraphQL\Queries\Common\Localization\TranslatesSimpleQuery::class,
            App\GraphQL\Queries\BackOffice\Localization\TranslatesFilterableQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AdminRolesQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AdminRolesListQuery::class,

            App\GraphQL\Queries\BackOffice\Security\IpAccessQuery::class,

            App\GraphQL\Queries\BackOffice\Permissions\AvailableAdminGrantsQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AvailableEmployeeGrantsQuery::class,
        ],
        'mutation' => [
            // Calls Queues ---------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Calls\Queue\QueueUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Calls\Queue\QueueTransferToAgentMutation::class,
            App\GraphQL\Mutations\BackOffice\Calls\Queue\QueueTransferToDepartmentMutation::class,
            // ----------------------------------------------------------------------------

            // Department -----------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Departments\DepartmentCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Departments\DepartmentUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Departments\DepartmentDeleteMutation::class,
            // ----------------------------------------------------------------------------

            // Employees ------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Employees\EmployeeCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Employees\EmployeeUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Employees\EmployeeDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Employees\EmployeeChangeStatusMutation::class,
            // ----------------------------------------------------------------------------

            // Sips -----------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Sips\SipCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Sips\SipUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Sips\SipDeleteMutation::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Mutations\Common\Localization\SetLanguageMutation::class,

            // Admin ---------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Admins\AdminCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateProfileMutation::class,
            // ----------------------------------------------------------------------------

            // Schedules ------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Schedules\ScheduleUpdateMutation::class,
            // ----------------------------------------------------------------------------

            // Music ----------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Musics\MusicCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Musics\MusicDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Musics\MusicUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Musics\MusicUploadRecordMutation::class,
            App\GraphQL\Mutations\BackOffice\Musics\MusicDeleteRecordMutation::class,
            App\GraphQL\Mutations\BackOffice\Musics\MusicToggleActiveMutation::class,
            // ----------------------------------------------------------------------------

            // Avatars (admin) ------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Admins\Avatars\AvatarUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\Avatars\AvatarDeleteMutation::class,
            // ----------------------------------------------------------------------------

            // Auth ------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Auth\LoginMutation::class,
            App\GraphQL\Mutations\BackOffice\Auth\ForgotPasswordMutation::class,
            App\GraphQL\Mutations\BackOffice\Auth\ResetPasswordMutation::class,
            App\GraphQL\Mutations\BackOffice\Auth\CheckPasswordTokenMutation::class,

            App\GraphQL\Mutations\BackOffice\Auth\Employee\EmployeeLogoutMutation::class,
            App\GraphQL\Mutations\BackOffice\Auth\Employee\EmployeeTokenRefreshMutation::class,
            App\GraphQL\Mutations\BackOffice\Auth\Employee\EmployeeChangePasswordMutation::class,

            App\GraphQL\Mutations\BackOffice\Auth\Admin\AdminTokenRefreshMutation::class,
            App\GraphQL\Mutations\BackOffice\Auth\Admin\AdminLogoutMutation::class,
            App\GraphQL\Mutations\BackOffice\Auth\Admin\AdminChangePasswordMutation::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Mutations\BackOffice\Localization\CreateOrUpdateTranslateMutation::class,
            App\GraphQL\Mutations\BackOffice\Localization\DeleteTranslateMutation::class,

            // Roles ---------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleToggleActiveMutation::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Mutations\BackOffice\Security\IpAccessCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Security\IpAccessUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Security\IpAccessDeleteMutation::class,

        ],
        'subscription' => [],
        'middleware' => [
            App\GraphQL\Middlewares\Security\IpAccessMiddleware::class,
        ],
        'method' => ['post'],
    ],
];

