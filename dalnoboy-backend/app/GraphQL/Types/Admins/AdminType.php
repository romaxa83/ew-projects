<?php

namespace App\GraphQL\Types\Admins;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\PhoneType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use GraphQL\Type\Definition\Type;

class AdminType extends BaseType
{
    public const NAME = 'AdminType';
    public const MODEL = Admin::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
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
                    'resolve' => fn(Admin $admin) => $admin->phone->phone,
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
                    'resolve' => static fn(Admin $admin) => $admin->role,
                ],
                'language' => [
                    'type' => LanguageType::type(),
                    'is_relation' => true,
                ],
            ]
        );
    }
}
