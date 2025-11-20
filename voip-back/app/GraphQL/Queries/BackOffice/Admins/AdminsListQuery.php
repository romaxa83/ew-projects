<?php

namespace App\GraphQL\Queries\BackOffice\Admins;

use App\GraphQL\Types\Admins\AdminSimpleType;
use App\Permissions\Admins\AdminListPermission;
use App\Repositories\Admins\AdminRepository;
use Core\GraphQL\Queries\BaseQuery;
use Illuminate\Support\Collection;
use GraphQL\Type\Definition\{ResolveInfo, Type};
use Rebing\GraphQL\Support\SelectFields;

class AdminsListQuery extends BaseQuery
{
    public const NAME = 'AdminsList';
    public const PERMISSION = AdminListPermission::KEY;

    public function __construct(protected AdminRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return AdminSimpleType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getListFromRole(
            $this->user(),
            $fields->getSelect() ?: ['id'],
            $fields->getRelations(),
            $args
        );
    }
}
