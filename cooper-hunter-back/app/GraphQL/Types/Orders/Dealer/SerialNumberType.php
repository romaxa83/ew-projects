<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\Models\Orders\Dealer\SerialNumber;
use GraphQL\Type\Definition\Type;

class SerialNumberType extends BaseType
{
    public const NAME = 'dealerOrderSerialNumberType';
    public const MODEL = SerialNumber::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'product' => [
                'type' => ProductType::Type(),
            ],
            'serial_number' => [
                'type' => Type::string(),
            ],
        ];
    }
}
