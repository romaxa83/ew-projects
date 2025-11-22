<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\Models\Orders\Dealer\Dimensions;
use GraphQL\Type\Definition\Type;

class DimensionsType extends BaseType
{
    public const NAME = 'dealerOrderPackingSlipDimensionType';
    public const MODEL = Dimensions::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'pallet' => [
                'type' => Type::int(),
            ],
            'box_qty' => [
                'type' => Type::int(),
            ],
            'type' => [
                'type' => Type::string(),
            ],
            'weight' => [
                'type' => Type::float(),
            ],
            'width' => [
                'type' => Type::float(),
            ],
            'depth' => [
                'type' => Type::float(),
            ],
            'height' => [
                'type' => Type::float(),
            ],
            'class_freight' => [
                'type' => Type::int(),
            ],
        ];
    }
}
