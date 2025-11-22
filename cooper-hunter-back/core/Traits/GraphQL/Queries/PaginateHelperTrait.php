<?php

namespace Core\Traits\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Rebing\GraphQL\Support\Facades\GraphQL;

trait PaginateHelperTrait
{
    protected function paginationArgs(): array
    {
        return [
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => config('queries.default.pagination.per_page')
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
        ];
    }

    protected function paginationRules(): array
    {
        return [
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
        ];
    }

    protected function paginate(Builder|QueryBuilder|Relation $builder, array $args): LengthAwarePaginator
    {
        return $builder->paginate(...$this->getPaginationParameters($args));
    }

    protected function getPaginationParameters(array $args): array
    {
        return [
            $this->getPerPage($args),
            ['*'],
            'page',
            $this->getPage($args)
        ];
    }

    protected function getPerPage(array $args, int $default = null): int
    {
        if (is_null($default)) {
            $default = config('queries.default.pagination.per_page');
        }

        return $args['per_page'] ?? $default;
    }

    protected function getPage(array $args): int
    {
        return $args['page'] ?? 1;
    }

    protected function paginateType(Type $type): Type
    {
        return GraphQL::paginate($type);
    }
}
