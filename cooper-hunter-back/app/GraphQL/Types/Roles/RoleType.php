<?php

namespace App\GraphQL\Types\Roles;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Permissions\Role;
use GraphQL\Type\Definition\Type;

class RoleType extends BaseType
{
    public const NAME = 'RoleType';
    public const MODEL = Role::class;

    public function fields(): array
    {
        $fields = [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'name' => [
                'type' => NonNullType::string(),
                'is_relation' => true,
            ],
            'translation' => [
                'type' => Type::nonNull(RoleTranslateType::type()),
                'is_relation' => true,
            ],
            'translations' => [
                'type' => Type::nonNull(Type::listOf(RoleTranslateType::type())),
                'is_relation' => true,
            ],
            'permissions' => [
                'type' => Type::nonNull(Type::listOf(PermissionType::type())),
                'is_relation' => true,
            ]
        ];

        return array_merge(
            parent::fields(),
            $fields
        );
    }
}
