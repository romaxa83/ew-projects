<?php

namespace App\GraphQL\Queries\FrontOffice\Employees;

use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Employees\EmployeeListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class EmployeesQueryForCompany extends BaseQuery
{
    public const NAME = 'employeesForCompany';
    public const PERMISSION = EmployeeListPermission::KEY;

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->sortArgs(),
            [
                'query' => Type::string(),
                'id' => Type::id(),
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
                ->whereSameCompany($this->manager())
                ->filter($args)
                ->with($fields->getRelations())
                ->with(
                    [
                        'companyUser',
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
                'id' => ['nullable', 'int'],
            ]
        );
    }
}
