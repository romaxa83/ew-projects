<?php

namespace App\GraphQL\Types\Orders\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Categories\OrderCategoryTranslation;
use GraphQL\Type\Definition\Type;

class OrderCategoryTranslateType extends BaseType
{
    public const NAME = 'OrderCategoryTranslateType';
    public const MODEL = OrderCategoryTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
