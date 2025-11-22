<?php

namespace App\GraphQL\Types\Catalog\Filters;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class FilterType extends BaseType
{
    public const NAME = 'FilterType';

    public function fields(): array
    {
        return [
            'feature_name' => [
                'type' => NonNullType::string(),
            ],
            'feature_short_name' => [
                'type' => NonNullType::string(),
                'resolve' => static fn($item): string => data_get(
                    $item,
                    'feature_short_name',
                    data_get($item, 'feature_name')
                )
            ],
            'values' => [
                'type' => FilterValueType::nonNullList(),
            ],
        ];
    }
}
