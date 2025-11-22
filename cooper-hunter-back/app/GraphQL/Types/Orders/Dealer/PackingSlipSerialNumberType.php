<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\Models\Orders\Dealer\PackingSlipSerialNumber;
use GraphQL\Type\Definition\Type;

class PackingSlipSerialNumberType extends BaseType
{
    public const NAME = 'dealerOrderPackingSlipSerialNumberType';
    public const MODEL = PackingSlipSerialNumber::class;

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
