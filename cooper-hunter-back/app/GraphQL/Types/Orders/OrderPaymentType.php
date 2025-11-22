<?php

namespace App\GraphQL\Types\Orders;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Orders\OrderCostStatusTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\OrderPayment;
use GraphQL\Type\Definition\Type;

class OrderPaymentType extends BaseType
{

    public const NAME = 'OrderPaymentType';
    public const MODEL = OrderPayment::class;

    public function fields(): array
    {
        return [
            'cost_status' => [
                'type' => OrderCostStatusTypeEnum::nonNullType()
            ],
            'order_id' => [
                'type' => NonNullType::id(),
            ],
            'order_price' => [
                'type' => Type::float(),
            ],
            'order_price_with_discount' => [
                'type' => Type::float(),
            ],
            'shipping_cost' => [
                'type' => Type::float()
            ],
            'tax' => [
                'type' => Type::float()
            ],
            'discount' => [
                'type' => Type::float()
            ],
            'paid_at' => [
                'type' => Type::int()
            ]
        ];
    }
}
