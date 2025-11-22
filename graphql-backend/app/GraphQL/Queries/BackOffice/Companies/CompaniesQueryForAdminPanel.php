<?php

namespace App\GraphQL\Queries\BackOffice\Companies;

use App\GraphQL\Types\Companies\CompanyType;
use App\Models\{Companies\Company};
use App\Permissions\Companies\CompanyAdminListPermission;
use Core\GraphQL\{Queries\BaseQuery};
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CompaniesQueryForAdminPanel extends BaseQuery
{
    public const NAME = 'companiesForAdminPanel';
    public const PERMISSION = CompanyAdminListPermission::KEY;
    public const DESCRIPTION = 'Query для получения информации о компаниях в админ-панели';

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
                'name' => Type::string(),
                'email' => Type::string(),
                'status' => Type::string(),
                'query' => Type::string(),
                'only_new' => Type::boolean(),
                'id' => Type::id(),
            ]
        );
    }

    public function type(): Type
    {
        return $this->paginateType(
            CompanyType::type()
        );
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): LengthAwarePaginator
    {
        return $this->paginate(
            Company::query()
                ->select($fields->getSelect() ?: ['id'])
                ->with($fields->getRelations())
                ->filter($args)
                ->with(
                    [
                        'owner',
                    ]
                ),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            $this->sortRules(),
            [
                'name' => ['nullable', 'string'],
                'email' => ['nullable', 'string'],
                'status' => ['nullable', 'string'],
                'query' => ['nullable', 'string'],
                'only_new' => ['nullable', 'boolean'],
                'id' => ['nullable', 'int'],
            ]
        );
    }

    protected function allowedForSortFields(): array
    {
        return Company::ALLOWED_SORTING_FIELDS;
    }
}
