<?php

namespace App\GraphQL\Types\Admins;

use App\GraphQL\ScalarTypes\EmailType;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use GraphQL\Type\Definition\Type;

class AdminType extends BaseType
{
    public const NAME = 'AdminType';

    public const MODEL = Admin::class;

    public function fields(): array
    {
        $fields = parent::fields();

        $fields['email'] = [
            'type' => Type::nonNull(new EmailType()),
        ];

        $fields['name'] = [
            'type' => Type::nonNull(Type::string()),
        ];

        $fields['roles'] = [
            'type' => Type::listOf(RoleType::type()),
        ];

        return $fields;
    }
}
