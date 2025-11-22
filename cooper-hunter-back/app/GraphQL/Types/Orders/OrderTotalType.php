<?php

namespace App\GraphQL\Types\Orders;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class OrderTotalType extends BaseType
{

    public const NAME = 'OrderTotalType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::int(),
            ],
            'history' => [
                'type' => NonNullType::int(),
            ],
            'total' => [
                'type' => NonNullType::int(),
            ]
        ];
    }
}
