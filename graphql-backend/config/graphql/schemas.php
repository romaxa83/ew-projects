<?php

declare(strict_types=1);

use App\GraphQL\Mutations\Common\Localization\SetLanguageMutation;
use App\GraphQL\Queries\BackOffice\Localization\TranslatesFilterableQuery;
use App\GraphQL\Queries\Common\Localization\LanguagesQuery;
use App\GraphQL\Queries\Common\Localization\TranslatesSimpleQuery;

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

return [
    'default' => [
        'query' => [
            LanguagesQuery::class,
            TranslatesSimpleQuery::class,
            App\GraphQL\Queries\FrontOffice\Permissions\EmployeeRolesQueryForCompany::class,
            App\GraphQL\Queries\FrontOffice\Users\UserProfileQuery::class,
            App\GraphQL\Queries\FrontOffice\Employees\EmployeesQueryForCompany::class,
        ],
        'mutation' => [
            SetLanguageMutation::class,

            App\GraphQL\Mutations\FrontOffice\Users\UserLoginMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserTokenRefreshMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserLogoutMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserRegisterMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserEmailVerificationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserResendVerificationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserChangePasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserForgotPasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserResetPasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Employees\EmployeeCreateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Employees\EmployeeUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Employees\EmployeeDeleteMutation::class,
            App\GraphQL\Mutations\FrontOffice\Companies\CompanyUpdateMutation::class,
        ],
        'subscription' => [
            App\GraphQL\Subscriptions\FrontOffice\Notifications\NotificationSubscription::class
        ],
        'middleware' => [],
        'method' => ['POST'],
        'execution_middleware' => null,
    ],
    'BackOffice' => [
        'query' => [
            LanguagesQuery::class,
            TranslatesSimpleQuery::class,
            TranslatesFilterableQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AdminRolesQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\EmployeeRolesQueryForAdmin::class,
            App\GraphQL\Queries\BackOffice\Users\UsersQueryForAdminPanel::class,
            App\GraphQL\Queries\BackOffice\Admins\AdminsQuery::class,
            App\GraphQL\Queries\BackOffice\Admins\AdminProfileQuery::class,
            App\GraphQL\Queries\BackOffice\Employees\EmployeesQueryForAdmin::class,
            App\GraphQL\Queries\BackOffice\Companies\CompaniesQueryForAdminPanel::class,
            App\GraphQL\Queries\BackOffice\Security\IpAccessQuery::class,

            App\GraphQL\Queries\BackOffice\Permissions\AvailableAdminGrantsQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AvailableUserGrantsQuery::class,
        ],
        'mutation' => [
            SetLanguageMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminTokenRefreshMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminLogoutMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminChangePasswordMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminLoginAsUserMutation::class,

            App\GraphQL\Mutations\BackOffice\Localization\CreateOrUpdateTranslateMutation::class,
            App\GraphQL\Mutations\BackOffice\Localization\DeleteTranslateMutation::class,

            App\GraphQL\Mutations\BackOffice\Permission\EmployeeRoleCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\EmployeeRoleUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\EmployeeRoleDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\EmployeeRoleSetDefaultForOwnerMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Security\IpAccessCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Security\IpAccessUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Security\IpAccessDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Users\UserDeleteMutation::class,
        ],
        'subscription' => [
            App\GraphQL\Subscriptions\BackOffice\Notifications\NotificationSubscription::class,
        ],
        'middleware' => [
            App\GraphQL\Middlewares\Security\IpAccessMiddleware::class,
        ],
        'method' => ['POST'],
        'execution_middleware' => null,
    ],
];
