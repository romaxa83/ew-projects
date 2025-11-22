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
        $fields = parent::fields();

        $fields['email'] = [
            'type' => NonNullType::string(),
        ];

        $fields['name'] = [
            'type' => Type::nonNull(Type::string()),
        ];

        $fields['language'] = [
            'type' => LanguageType::type(),
            'is_relation' => true,
        ];

        $fields['roles'] = [
            'type' => Type::listOf(RoleType::type()),
        ];

        $fields['permissions'] = [
            'type' => Type::listOf(PermissionType::type()),
        ];

        return $fields;
    }

    protected function resolvePermissionsField(Admin $root, $args): Collection
    {
        return $root->getAllPermissions();
    }
}
