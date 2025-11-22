<?php

namespace App\GraphQL\Types\Orders;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Orders\OrderStatusTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Orders\OrderStatusHistory;

class OrderStatusHistoryType extends BaseType
{

    public const NAME = 'OrderStatusHistoryType';
    public const MODEL = OrderStatusHistory::class;

    public function fields(): array
    {
        return [
            'status' => [
                'type' => OrderStatusTypeEnum::nonNullType(),
            ],
            'changer' => [
                'type' => UserMorphType::nonNullType(),
                'description' => 'User who set changing in order status.',
                'is_relation' => true
            ],
            'created_at' => [
                'type' => NonNullType::int(),
                'resolve' => fn(OrderStatusHistory $orderStatusHistory) => $orderStatusHistory
                    ->created_at
                    ->getTimestamp()
            ]
        ];
    }
}
