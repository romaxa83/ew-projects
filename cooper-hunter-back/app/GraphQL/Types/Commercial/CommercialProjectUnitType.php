<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProjectUnit;

class CommercialProjectUnitType extends BaseType
{
    public const NAME = 'CommercialProjectUnitType';
    public const MODEL = CommercialProjectUnit::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'product' => [
                    'type' => ProductType::type(),
                    'is_relation' => true,
                ],
                'serial_number' => [
                    'type' => NonNullType::string(),
                ],
            ],
        );
    }
}
