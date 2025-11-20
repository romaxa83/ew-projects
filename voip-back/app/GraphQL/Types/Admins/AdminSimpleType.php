<?php

namespace App\GraphQL\Types\Admins;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;

class AdminSimpleType extends BaseType
{
    public const NAME = 'AdminSimpleType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'name' => [
                'type' => NonNullType::string()
            ],
            'role' => [
                'type' => NonNullType::string(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => static fn(Admin $a) => $a->role->name
            ],
        ];
    }
}
