<?php

namespace App\GraphQL\Types\Catalog\Manuals\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class ManualCategoryProductGroupType extends BaseType
{
    public const NAME = 'ManualCategoryProductGroupType';

    public function fields(): array
    {
        return [
            'category_name' => [
                'type' => NonNullType::string(),
            ],
            'products' => [
                'type' => ManualCategoryProductListType::nonNullList(),
            ],
        ];
    }
}
