<?php

namespace App\GraphQL\Types\Auth;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\PermissionType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;

class AuthProfileType extends BaseType
{
    public const NAME = 'AuthProfileType';

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
                'language' => [
                    'type' => LanguageType::type(),
                    'is_relation' => true,
                ],
                'roles' => [
                    'type' => Type::listOf(RoleType::type()),
                ],
                'permissions' => [
                    'type' => Type::listOf(PermissionType::type()),
                ],
            ]
        );
    }

    protected function resolvePermissionsField(Admin|Employee $root, $args): Collection
    {
        return $root->getAllPermissions();
    }

    protected function resolveNameField(Admin|Employee $root, $args): string
    {
        if($root instanceof Admin){
            return $root->name;
        }

        return $root->getName();
    }
}
