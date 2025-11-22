<?php

namespace App\GraphQL\Types\Wrappers\Catalog;

use App\GraphQL\Types\Wrappers\PaginationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchResultPaginationType extends PaginationType
{
    protected function getPaginationFields(string $typeName): array
    {
        return array_merge(
            parent::getPaginationFields($typeName),
            [
                'meta' => [
                    'type' => SearchResultPaginationMeta::type($typeName),
                    'description' => 'Pagination meta data',
                    'selectable' => false,
                    'resolve' => function (LengthAwarePaginator $data): LengthAwarePaginator {
                        return $data;
                    }
                ]
            ]
        );
    }
}
