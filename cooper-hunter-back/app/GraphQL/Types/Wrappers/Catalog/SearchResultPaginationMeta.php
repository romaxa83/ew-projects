<?php

namespace App\GraphQL\Types\Wrappers\Catalog;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class SearchResultPaginationMeta
{
    public static function type(string $typeName): Type
    {
        return GraphQL::wrapType(
            $typeName,
            'SearchResultPaginationMeta',
            SearchResultPaginationMetaType::class
        );
    }
}
