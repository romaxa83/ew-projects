<?php

namespace App\GraphQL\Queries\Common\Permissions;

use App\GraphQL\Types\Roles\RoleType;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseRolesQuery extends BaseQuery
{
    public const PERMISSION = RoleListPermission::KEY;

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->sortArgs(),
            [
                'id' => Type::id(),
                'title' => Type::string(),
            ]
        );
    }

    public function type(): Type
    {
        return $this->paginateType(
            RoleType::type()
        );
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->paginate(
            Role::query()
                ->select($fields->getSelect() ?: ['id'])
                ->withoutSuperAdmin()
                ->latest()
                ->with($fields->getRelations())
                ->where('guard_name', $this->getRoleGuard())
                ->filter($args),
            $args
        );
    }

    abstract protected function getRoleGuard(): string;

    protected function allowedForSortFields(): array
    {
        return Role::ALLOWED_SORTING_FIELDS;
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            $this->sortRules(),
            [
                'id' => ['nullable', 'int'],
                'title' => ['nullable', 'string'],
            ]
        );
    }
}
