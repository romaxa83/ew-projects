<?php

namespace App\GraphQL\Queries\BackOffice\Departments;

use App\GraphQL\Types\Departments\DepartmentType;
use App\Models\Departments\Department;
use App\Repositories\Departments\DepartmentRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class DepartmentsQuery extends BaseQuery
{
    public const NAME = 'Departments';
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
        return array_merge(
            $this->paginationArgs(),
            [
                'id' => Type::id(),
                'active' => Type::boolean(),
            ],
        );
    }

    public function type(): Type
    {
        return DepartmentType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->repo->getPagination(
            filters: $args
        );
    }
}
