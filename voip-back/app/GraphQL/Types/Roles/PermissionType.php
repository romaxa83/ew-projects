<?php

namespace App\GraphQL\Types\Roles;

use App\GraphQL\Types\BaseType;
use App\Models\Permissions\Permission;
use GraphQL\Type\Definition\Type;

class PermissionType extends BaseType
{
    public const NAME = 'PermissionType';
    public const MODEL = Permission::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'deprecationReason' => 'ID для permission запрашивать нельзя',
            ],
            'name' => [
                'type' => Type::string(),
            ],
        ];
    }
}
