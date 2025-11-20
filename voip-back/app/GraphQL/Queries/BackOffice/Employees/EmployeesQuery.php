<?php

namespace App\GraphQL\Queries\BackOffice\Employees;

use App\GraphQL\Types\Employees\EmployeeType;
use App\GraphQL\Types\Enums\Employees\StatusEnum;
use App\Repositories\Employees\EmployeeRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class EmployeesQuery extends BaseQuery
{
    public const NAME = 'Employees';
    public const PERMISSION = Permissions\Employees\ListPermission::KEY;

    public function __construct(protected EmployeeRepository $repo)
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
                'status' => StatusEnum::type(),
                'department_id' => Type::id(),
                'sip_id' => Type::id(),
                'search' => Type::string(),
            ],
        );
    }

    public function type(): Type
    {
        return EmployeeType::paginate();
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
