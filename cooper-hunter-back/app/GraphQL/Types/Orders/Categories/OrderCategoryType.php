<?php

namespace App\GraphQL\Types\Orders\Categories;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Categories\OrderCategory;

class OrderCategoryType extends BaseType
{
    public const NAME = 'OrderCategoryType';
    public const MODEL = OrderCategory::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'sort' => [
                    'type' => NonNullType::int()
                ],
                'active' => [
                    'type' => NonNullType::boolean()
                ],
                'need_description' => [
                    'type' => NonNullType::boolean(),
                    'description' => 'Mark for category which requires additional information at checkout by technician',
                ],
                'translation' => [
                    'type' => OrderCategoryTranslateType::nonNullType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => NonNullType::listOf(OrderCategoryTranslateType::nonNullType()),
                    'is_relation' => true,
                ],
            ]
        );
    }


}
