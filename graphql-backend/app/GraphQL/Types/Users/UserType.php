<?php

namespace App\GraphQL\Types\Users;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Users\User;
use Core\GraphQL\Fields\PermissionField;
use GraphQL\Type\Definition\Type;

class UserType extends BaseType
{
    public const NAME = 'UserType';
    public const MODEL = User::class;

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
                'middle_name' => [
                    'type' => NonNullType::string(),
                ],
                'email' => [
                    'type' => NonNullType::string(),
                ],
                'lang' => [
                    'type' => Type::string(),
                ],
                'permission' => PermissionField::class,
                'company' => [
                    'type' => Type::nonNull(CompanyType::type()),
                ],
                'roles' => [
                    'type' => Type::listOf(RoleType::type()),
                ]
            ]
        );
    }
}
