<?php

namespace App\GraphQL\Types\Wrappers;

use GraphQL\Type\Definition\Type as GraphQLType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\PaginationType as OriginPaginationType;

class PaginationType extends OriginPaginationType
{
    protected function getPaginationFields(string $typeName): array
    {
        return [
            'data' => [
                'type' => GraphQLType::listOf(GraphQL::type($typeName)),
                'description' => 'List of items on the current page',
                'resolve' => function (LengthAwarePaginator $data): Collection|array {
                    return $data->items();
                },
            ],
            'meta' => [
                'type' => PaginationMeta::type($typeName),
                'description' => 'Pagination meta data',
                'selectable' => false,
                'resolve' => function (LengthAwarePaginator $data): LengthAwarePaginator {
                    return $data;
                }
            ],
        ];
    }
}
