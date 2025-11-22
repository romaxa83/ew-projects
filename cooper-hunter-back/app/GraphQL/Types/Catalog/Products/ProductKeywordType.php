<?php

namespace App\GraphQL\Types\Catalog\Products;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\ProductKeyword;

class ProductKeywordType extends BaseType
{
    public const NAME = 'ProductKeywordType';
    public const MODEL = ProductKeyword::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'keyword' => [
                'type' => NonNullType::string(),
            ]
        ];
    }
}