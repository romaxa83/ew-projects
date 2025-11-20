<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use App\Permissions\Roles\RoleListPermission;
use App\Repositories\Permissions\RoleRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class AdminRolesListQuery extends BaseQuery
{
    public const NAME = 'AdminRolesList';
    public const PERMISSION = RoleListPermission::KEY;

    public function __construct(protected RoleRepository $repo)
    {
        $this->setAdminGuard();
    }

    protected function getRoleGuard(): string
    {
        return Admin::GUARD;
    }

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
        return RoleType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getCustomList(
            $fields->getSelect() ?: ['id'],
            $args,
            $fields->getRelations(),
            $this->getRoleGuard()
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->sortRules(),
            [
                'id' => ['nullable', 'int'],
                'title' => ['nullable', 'string'],
            ]
        );
    }
}
