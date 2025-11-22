<?php

namespace App\GraphQL\Types\Catalog\Manuals\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class ManualCategoryProductListType extends BaseType
{
    public const NAME = 'ManualCategoryProductListType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Product ID'
            ],
            'title' => [
                'type' => NonNullType::string(),
                'description' => 'Product Title (name)'
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
