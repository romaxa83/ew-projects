<?php

namespace App\GraphQL\Types\Catalog\Manuals\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class ManualCategoryType extends BaseType
{
    public const NAME = 'ManualCategoryType';

    public function fields(): array
    {
        return [
            'category_id' => [
                'type' => NonNullType::id(),
            ],
            'category_name' => [
                'type' => NonNullType::string(),
            ],
            'sub_categories' => [
                'type' => ManualSubcategoryType::nonNullList(),
            ],
        ];
    }
}
