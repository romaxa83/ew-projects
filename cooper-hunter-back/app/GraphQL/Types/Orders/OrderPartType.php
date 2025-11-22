<?php

namespace App\GraphQL\Types\Orders;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\OrderPart;
use GraphQL\Type\Definition\Type;

class OrderPartType extends BaseType
{

    public const NAME = 'OrderPartType';
    public const MODEL = OrderPart::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'alias' => 'order_category_id',
            ],
            'name' => [
                'type' => NonNullType::string(),
                'resolve' => fn (OrderPart $orderPart) => $orderPart->translation()->title
            ],
            'quantity' => [
                'type' => NonNullType::int(),
                'defaultValue' => config('orders.categories.default_quantity'),
            ],
            'price' => [
                'type' => Type::float(),
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'Additional data about "other" category'
            ]
        ];
    }
}
