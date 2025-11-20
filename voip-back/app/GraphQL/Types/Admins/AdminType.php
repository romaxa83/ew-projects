<?php

namespace App\GraphQL\Types\Admins;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
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
                'email' => [
                    'type' => NonNullType::string(),
                ],
                'name' => [
                    'type' => NonNullType::string()
                ],
                'roles' => [
                    'type' => Type::listOf(RoleType::type()),
                ],
                'active' => [
                    'type' => Type::boolean(),
                ],
                'notify' => [
                    'type' => Type::boolean(),
                ],
                'relations' => [
                    'type' => self::list(),
                    'is_relation' => true,
                    'alias' => 'relationAdmins'
                ],
                'avatar' => [
                    'type' => MediaType::type(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => static fn(Admin $a) => $a->avatar()
                ],
            ]
        );
    }
}
