<?php

namespace App\GraphQL\Types\Orders;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class OrderCounterType extends BaseType
{

    public const NAME = 'OrderCounterType';

    public function fields(): array
    {
        return [
            'created' => [
                'type' => NonNullType::int(),
            ],
            'pending_paid' => [
                'type' => NonNullType::int(),
            ],
            'paid' => [
                'type' => NonNullType::int(),
            ],
            'shipped' => [
                'type' => NonNullType::int(),
            ],
            'canceled' => [
                'type' => NonNullType::int(),
            ],
            'total' => [
                'type' => NonNullType::int(),
            ]
        ];
    }
}
