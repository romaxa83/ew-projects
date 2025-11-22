<?php

namespace App\GraphQL\InputTypes\Orders\BackOffice;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class OrderPaymentBackOfficeInput extends BaseInputType
{
    public const NAME = 'OrderPaymentBackOfficeInput';

    public function fields(): array
    {
        return [
            'order_price' => [
                'type' => NonNullType::float(),
            ],
            'order_price_with_discount' => [
                'type' => NonNullType::float(),
            ],
            'shipping_cost' => [
                'type' => NonNullType::float(),
            ],
            'tax' => [
                'type' => NonNullType::float(),
            ],
            'discount' => [
                'type' => NonNullType::float(),
            ],
            'paid_at' => [
                'type' => Type::int(),
            ]
        ];
    }
}
