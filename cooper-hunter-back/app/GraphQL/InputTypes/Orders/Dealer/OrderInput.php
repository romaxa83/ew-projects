<?php

namespace App\GraphQL\InputTypes\Orders\Dealer;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Orders\Dealer\DeliveryTypeTypeEnum;
use App\GraphQL\Types\Enums\Orders\Dealer\OrderTypeTypeEnum;
use App\GraphQL\Types\Enums\Orders\Dealer\PaymentTypeTypeEnum;
use GraphQL\Type\Definition\Type;

class OrderInput extends BaseInputType
{
    public const NAME = 'DealerOrderInput';

    public function fields(): array
    {
        return [
            'delivery_type' => [
                'type' => DeliveryTypeTypeEnum::Type(),
            ],
            'payment_type' => [
                'type' => PaymentTypeTypeEnum::Type(),
            ],
            'type' => [
                'type' => OrderTypeTypeEnum::Type(),
            ],
            'po' => [
                'type' => Type::string(),
            ],
            'comment' => [
                'type' => Type::string(),
            ],
            'shipping_address_id' => [
                'type' => Type::id(),
            ],
            'payment_card_id' => [
                'type' => Type::id(),
            ]
        ];
    }
}
