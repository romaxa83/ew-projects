<?php

namespace App\GraphQL\Types\Admins;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\PermissionType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;

class AdminProfileType extends BaseType
{
    public const NAME = 'AdminProfileType';
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
                'permissions' => [
                    'type' => Type::listOf(PermissionType::type()),
                ],
            ]
        );
    }

    protected function resolvePermissionsField(Admin $root, $args): Collection
    {
        return $root->getAllPermissions();
    }
}
