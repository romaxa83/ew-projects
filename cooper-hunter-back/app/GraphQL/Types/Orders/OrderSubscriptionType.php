<?php

namespace App\GraphQL\Types\Orders;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Orders\OrderSubscriptionActionTypeEnum;
use App\GraphQL\Types\NonNullType;

class OrderSubscriptionType extends BaseType
{
    public const NAME = 'OrderSubscriptionType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Order ID'
            ],
            'action' => [
                'type' => OrderSubscriptionActionTypeEnum::nonNullType(),
            ]
        ];
    }
}
