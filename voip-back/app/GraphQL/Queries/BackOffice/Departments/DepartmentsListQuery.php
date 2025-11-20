<?php

namespace App\GraphQL\Queries\BackOffice\Departments;

use App\GraphQL\Types\Departments\DepartmentType;
use App\Repositories\Departments\DepartmentRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class DepartmentsListQuery extends BaseQuery
{
    public const NAME = 'DepartmentsList';
    public const PERMISSION = Permissions\Departments\ListPermission::KEY;

    public function __construct(protected DepartmentRepository $repo)
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        $this->setAuthGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => Type::id(),
            'active' => Type::boolean(),
        ];
    }

    public function type(): Type
    {
        return DepartmentType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getList(
            filters: $args
        );
    }
}

