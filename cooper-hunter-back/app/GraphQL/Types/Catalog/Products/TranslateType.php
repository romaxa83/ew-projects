<?php

namespace App\GraphQL\Types\Catalog\Products;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\ProductTranslation;
use GraphQL\Type\Definition\Type;

class TranslateType extends BaseType
{
    public const NAME = 'ProductTranslateType';
    public const MODEL = ProductTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
            'seo_title' => [
                'type' => Type::string(),
            ],
            'seo_description' => [
                'type' => Type::string(),
            ],
            'seo_h1' => [
                'type' => Type::string(),
            ],
        ];
    }
}
