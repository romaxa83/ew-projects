<?php

namespace App\GraphQL\Queries\Common\Permissions;

use App\GraphQL\Types\Roles\RoleType;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseRolesQuery extends BaseQuery
{
    public const PERMISSION = RoleListPermission::KEY;

    public function args(): array
    {
        return array_merge(
            $this->buildArgs(Role::ALLOWED_SORTING_FIELDS),
            [
                'title' => [
                    'type' => Type::string()
                ],
            ]
        );
    }

    public function type(): Type
    {
        return RoleType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Role::query()
            ->select($fields->getSelect() ?: ['id'])
            ->with($fields->getRelations())
            ->where('guard_name', static::getRoleGuard())
            ->filter($args)
            ->get();
    }

    abstract protected function getRoleGuard(): string;
}
