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

    // Departments --------------------------------------------------------------
    App\GraphQL\Types\Departments\DepartmentType::class,

    App\GraphQL\InputTypes\Departments\DepartmentInput::class,
    // --------------------------------------------------------------------------

    // Employees -----------------------------------------------------------------
    App\GraphQL\Types\Employees\EmployeeType::class,
    App\GraphQL\Types\Employees\EmployeeProfileType::class,

    App\GraphQL\Types\Enums\Employees\StatusEnum::class,

    App\GraphQL\InputTypes\Employees\EmployeeInput::class,
    // --------------------------------------------------------------------------

    // Sip ----------------------------------------------------------------------
    App\GraphQL\Types\Sips\SipType::class,

    App\GraphQL\InputTypes\Sips\SipInput::class,
    // --------------------------------------------------------------------------

    // Call History -------------------------------------------------------------
    App\GraphQL\Types\Enums\Calls\HistoryStatusEnum::class,

    App\GraphQL\Types\Calls\History\HistoryType::class,
    // --------------------------------------------------------------------------

    // Call Queue ---------------------------------------------------------------
    App\GraphQL\Types\Calls\Queue\QueueType::class,
    App\GraphQL\Types\Enums\Calls\QueueStatusEnum::class,
    App\GraphQL\Types\Enums\Calls\QueueTypeEnum::class,

    App\GraphQL\InputTypes\Calls\QueueInput::class,
    // --------------------------------------------------------------------------

    // Report -------------------------------------------------------------------
    App\GraphQL\Types\Enums\Reports\ReportStatusEnum::class,

    App\GraphQL\Types\Reports\ReportType::class,
    App\GraphQL\Types\Reports\ReportAdditionalType::class,
    App\GraphQL\Types\Reports\ReportItemAdditionalType::class,
    App\GraphQL\Types\Reports\ItemType::class,
    App\GraphQL\Types\Reports\PauseItemType::class,
    App\GraphQL\Types\Reports\ReportPauseItemAdditionalType::class,
    // --------------------------------------------------------------------------

    // Utilities ----------------------------------------------------------------
    App\GraphQL\Types\Enums\LanguageTypeEnum::class,
    // --------------------------------------------------------------------------

    // Media --------------------------------------------------------------------
    App\GraphQL\Types\Media\MediaType::class,
    App\GraphQL\Types\Media\MediaConversionType::class,
    // --------------------------------------------------------------------------

    // Music --------------------------------------------------------------------
    App\GraphQL\Types\Musics\MusicType::class,

    App\GraphQL\InputTypes\Musics\MusicInput::class,
    // --------------------------------------------------------------------------

    // Schedule -----------------------------------------------------------------
    App\GraphQL\Types\Enums\Schedules\DayEnumType::class,

    App\GraphQL\Types\Schedules\ScheduleType::class,
    App\GraphQL\Types\Schedules\DayType::class,
    App\GraphQL\Types\Schedules\AdditionDay::class,


    App\GraphQL\InputTypes\Schedules\DayInput::class,
    App\GraphQL\InputTypes\Schedules\AdditionDayInput::class,
    // --------------------------------------------------------------------------

    App\GraphQL\Types\UploadType::class,

    App\GraphQL\Types\Enums\Messages\MessageKindEnumType::class,
    App\GraphQL\Types\Enums\Messages\AlertTargetEnumType::class,

    App\GraphQL\Types\Enums\Permissions\AdminPermissionEnum::class,
    App\GraphQL\Types\Localization\LanguageType::class,
    App\GraphQL\Types\Localization\TranslateType::class,

    App\GraphQL\Types\Roles\RoleType::class,
    App\GraphQL\Types\Roles\RoleTranslateType::class,
    App\GraphQL\Types\Roles\RoleTranslateInputType::class,
    App\GraphQL\Types\Roles\PermissionType::class,
    App\GraphQL\Types\Roles\GrantType::class,
    App\GraphQL\Types\Roles\GrantGroupType::class,

    // Admin ---------------------------------------------------------
    App\GraphQL\Types\Admins\AdminType::class,
    App\GraphQL\Types\Admins\AdminProfileType::class,
    App\GraphQL\Types\Admins\AdminSimpleType::class,
    // ---------------------------------------------------------------

    // Auth ----------------------------------------------------------
    App\GraphQL\Types\Auth\AuthProfileType::class,
    App\GraphQL\Types\Auth\LoginTokenType::class,
    // ---------------------------------------------------------------

    App\GraphQL\Types\Unions\Authenticatable::class,

    App\GraphQL\Types\Messages\ResponseMessageType::class,
    App\GraphQL\Types\Messages\AlertMessageType::class,

    App\GraphQL\Types\Security\IpAccessType::class,

    App\GraphQL\Types\Files\FileType::class,

    App\GraphQL\Types\Enums\Avatars\AvatarModelsTypeEnum::class,
];
