<?php

namespace App\GraphQL\Queries\BackOffice\Security;

use App\GraphQL\Types\Security\IpAccessType;
use App\Models\Security\IpAccess;
use App\Permissions\Security\IpAccessListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class IpAccessQuery extends BaseQuery
{
    public const NAME = 'IpAccess';
    public const PERMISSION = IpAccessListPermission::KEY;

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
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Поисковый запрос.'
                ],
            ]
        );
    }

    public function type(): Type
    {
        return $this->paginateType(
            IpAccessType::type()
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
            IpAccess::query()
                ->select($fields->getSelect() ?: ['id'])
                ->latest()
                ->filter($args),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->guest()
            ? []
            : array_merge(
                $this->paginationRules(),
                $this->sortRules(),
                [
                    'query' => ['nullable', 'string',],
                ]
            );
    }

    protected function allowedForSortFields(): array
    {
        return IpAccess::ALLOWED_SORTING_FIELDS;
    }
}
