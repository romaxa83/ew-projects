<?php

namespace App\GraphQL\Types\Roles;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Permissions\Role;

class RoleType extends BaseType
{
    public const NAME = 'RoleType';
    public const MODEL = Role::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => NonNullType::string(),
                    'is_relation' => true,
                ],
                'translate' => [
                    'type' => RoleTranslateType::nonNullType(),
                    'is_relation' => true,
                ],
                'translates' => [
                    'type' => RoleTranslateType::nonNullList(),
                    'is_relation' => true,
                ],
                'permissions' => [
                    'type' => NonNullType::listOfString(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => static fn(Role $role) => $role->permissions->pluck('name')
                ]
            ]
        );
    }
}
