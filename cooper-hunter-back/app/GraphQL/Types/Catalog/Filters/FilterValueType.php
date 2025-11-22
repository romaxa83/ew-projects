<?php

namespace App\GraphQL\Types\Catalog\Filters;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class FilterValueType extends BaseType
{
    public const NAME = 'FilterValueType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
//            'is_selected' => [
//                'type' => NonNullType::boolean(),
//            ]
        ];
    }
}
