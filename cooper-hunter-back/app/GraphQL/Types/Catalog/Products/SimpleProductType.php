<?php

namespace App\GraphQL\Types\Catalog\Products;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;

class SimpleProductType extends BaseType
{
    public const NAME = 'SimpleProductType';
    public const MODEL = Product::class;

    public function fields(): array
    {
        $fields = [
            'id' => ['type' => NonNullType::id(),],
            'title' => ['type' => NonNullType::string()],
            'slug' => ['type' => NonNullType::string()],
            'translation' => [
                'type' => TranslateType::nonNullType(),
                'is_relation' => true,
            ],
            'translations' => [
                'type' => TranslateType::nonNullList(),
                'is_relation' => true,
            ],
        ];

        return array_merge(
            parent::fields(),
            $fields
        );
    }
}
