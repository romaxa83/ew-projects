<?php

namespace App\GraphQL\Types\Catalog\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class CategoryForSelectType extends BaseType
{
    public const NAME = 'CategoryForSelectType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'disabled' => [
                'type' => NonNullType::boolean(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
