<?php

declare(strict_types=1);

// The types available in the application. You can then access it from the
// facade like this: GraphQL::type('user')
//
// Example:
//
// 'types' => [
//     'user' => 'App\GraphQL\Type\UserType'
// ]

return [
    App\GraphQL\Types\UploadType::class,

    App\GraphQL\Types\Enums\Messages\MessageKindEnumType::class,
    App\GraphQL\Types\Enums\Messages\AlertTargetEnumType::class,

    App\GraphQL\Types\Enums\Permissions\AdminPermissionEnum::class,
    App\GraphQL\Types\Enums\Permissions\EmployeePermissionEnum::class,
    App\GraphQL\Types\Localization\LanguageType::class,
    App\GraphQL\Types\Localization\TranslateType::class,

    App\GraphQL\Types\Roles\RoleType::class,
    App\GraphQL\Types\Roles\RoleTranslateType::class,
    App\GraphQL\Types\Roles\RoleTranslateInputType::class,
    App\GraphQL\Types\Roles\PermissionType::class,
    App\GraphQL\Types\Roles\GrantType::class,
    App\GraphQL\Types\Roles\GrantGroupType::class,

    App\GraphQL\Types\Users\UserType::class,
    App\GraphQL\Types\Users\UserLoginType::class,
    App\GraphQL\Types\Users\UserProfileType::class,

    App\GraphQL\Types\Admins\AdminType::class,
    App\GraphQL\Types\Admins\AdminLoginType::class,
    App\GraphQL\Types\Admins\AdminProfileType::class,

    App\GraphQL\Types\Users\AdminUserLoginType::class,

    App\GraphQL\Types\Companies\CompanyType::class,
    App\GraphQL\Types\Companies\PublicCompanyInfoType::class,

    App\GraphQL\Types\Unions\Authenticatable::class,

    App\GraphQL\Types\Messages\ResponseMessageType::class,
    App\GraphQL\Types\Messages\AlertMessageType::class,

    App\GraphQL\Types\Security\IpAccessType::class,


    //dto

    App\Dto\Admins\AdminDto::class
];
