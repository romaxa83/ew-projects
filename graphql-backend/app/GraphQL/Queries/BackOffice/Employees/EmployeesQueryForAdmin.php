<?php

namespace App\GraphQL\Queries\BackOffice\Employees;

use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Employees\EmployeeListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class EmployeesQueryForAdmin extends BaseQuery
{
    public const NAME = 'employeesForAdmin';
    public const PERMISSION = EmployeeListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->sortArgs(),
            [
                'query' => Type::string(),
                'company' => Type::int(),
                'id' => Type::id(),
                'state' => Type::string()
            ]
        );
    }

    public function type(): Type
    {
        return $this->paginateType(
            UserType::type()
        );
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            User::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->with(
                    [
                        'companyUser',
                        'company.owner',
                    ]
                ),
            $args
        );
    }

    protected function allowedForSortFields(): array
    {
        return User::ALLOWED_SORTING_FIELDS;
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            $this->sortRules(),
            [
                'query' => ['nullable', 'string'],
                'company' => ['nullable', 'int', 'exists:companies,id'],
                'id' => ['nullable', 'int'],
                'state' => ['nullable', 'string']
            ]
        );
    }
}
