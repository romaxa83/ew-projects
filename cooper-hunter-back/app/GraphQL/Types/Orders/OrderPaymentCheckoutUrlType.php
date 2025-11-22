<?php

namespace App\GraphQL\Types\Orders;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class OrderPaymentCheckoutUrlType extends BaseType
{

    public const NAME = 'OrderPaymentCheckoutUrlType';

    public function fields(): array
    {
        return [
            'url' => [
                'type' => NonNullType::string(),
                'description' => 'The URL to which the technician have to redirected to.'
            ],
        ];
    }
}
