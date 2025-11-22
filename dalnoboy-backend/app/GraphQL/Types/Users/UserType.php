<?php

namespace App\GraphQL\Types\Users;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Branches\BranchType;
use App\GraphQL\Types\Enums\Users\AuthorizationExpirationPeriodEnumType;
use App\GraphQL\Types\Inspections\InspectionType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\PhoneType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Users\User;
use GraphQL\Type\Definition\Type;

class UserType extends BaseType
{
    public const NAME = 'UserType';
    public const MODEL = User::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
            ],
            'first_name' => [
                'type' => NonNullType::string(),
            ],
            'last_name' => [
                'type' => NonNullType::string(),
            ],
            'second_name' => [
                'type' => Type::string(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'phone' => [
                'type' => NonNullType::string(),
                'description' => 'Default phone',
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(User $user) => $user->phone->phone,
            ],
            'phones' => [
                'type' => PhoneType::nonNullList(),
                'description' => 'All phones list including default',
                'is_relation' => true,
            ],
            'role' => [
                'type' => RoleType::nonNullType(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => static fn(User $user) => $user->role,
            ],
            'language' => [
                'type' => LanguageType::type(),
                'is_relation' => true,
            ],
            'branch' => [
                'type' => BranchType::type(),
                'is_relation' => true,
            ],
            'authorization_expiration_period' => [
                'type' => AuthorizationExpirationPeriodEnumType::nonNullType(),
            ],
            'avatar' => [
                'type' => MediaType::type(),
                'selectable' => false,
                'resolve' => fn(User $user) => $user->avatar,
            ],
            'inspections' => [
                'type' => InspectionType::list(),
                'is_relation' => true,
            ],
            'inspections_count' => [
                'type' => Type::int(),
            ]
        ];
    }
}
