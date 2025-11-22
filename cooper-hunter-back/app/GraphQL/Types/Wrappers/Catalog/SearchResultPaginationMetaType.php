<?php

namespace App\GraphQL\Types\Wrappers\Catalog;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Wrappers\PaginationMetaType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchResultPaginationMetaType extends PaginationMetaType
{
    protected function getPaginationMetaFields(string $typeName): array
    {
        return array_merge(
            [
                'search_result' => [
                    'type' => NonNullType::string(),
                    'description' => 'Number of total products found',
                    'resolve' => static fn(LengthAwarePaginator $data): string => trans_choice(
                        'messages.products_count',
                        $count = $data->total(),
                        compact('count')
                    ),
                    'selectable' => false,
                ],
            ],
            parent::getPaginationMetaFields($typeName),
        );
    }
}
