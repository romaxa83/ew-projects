<?php

namespace App\GraphQL\Queries\BackOffice\Admins;

use App\GraphQL\Types\Admins\AdminType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\{ResolveInfo, Type};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class AdminsQuery extends BaseQuery
{
    public const NAME = 'admins';
    public const PERMISSION = AdminListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return $this->paginateType(
            AdminType::type()
        );
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->sortArgs(),
            [
                'id' => Type::id(),
                'query' => Type::string(),
            ]
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
            Admin::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations()),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            $this->sortRules(),
            [
                'id' => ['nullable', 'int'],
                'query' => ['nullable', 'string'],
            ]
        );
    }

    protected function allowedForSortFields(): array
    {
        return Admin::ALLOWED_SORTING_FIELDS;
    }
}
