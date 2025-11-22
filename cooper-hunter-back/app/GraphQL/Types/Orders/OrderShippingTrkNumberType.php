<?php

namespace App\GraphQL\Types\Orders;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class OrderShippingTrkNumberType extends BaseType
{

    public const NAME = 'OrderShippingTrkNumberType';

    public function fields(): array
    {
        return [
            'number' => [
                'type' => NonNullType::string(),
            ],
            'tracking_url' => [
                'type' => NonNullType::string(),
                'resolve' => fn (array $trkData) => !empty($trkData['number']) ? config('orders.tracking_url') . $trkData['number'] : null
            ]
        ];
    }
}
