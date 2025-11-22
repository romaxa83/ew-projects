<?php

namespace App\GraphQL\Types\Catalog\Tickets;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Categories\OrderCategory;
use GraphQL\Type\Definition\Type;

class TicketOrderCategoryType extends BaseType
{
    public const NAME = 'TicketOrderCategoryType';
    public const MODEL = OrderCategory::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'name' => [
                'type' => NonNullType::string(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(OrderCategory $orderCategory) => $orderCategory->translation->title
            ],
            'quantity' => [
                'type' => NonNullType::int(),
                'defaultValue' => config('orders.categories.default_quantity'),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(OrderCategory $orderCategory) => $orderCategory->pivot->quantity,
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'Additional data about "other" category',
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(OrderCategory $orderCategory) => $orderCategory->pivot->description,
            ]
        ];
    }
}
