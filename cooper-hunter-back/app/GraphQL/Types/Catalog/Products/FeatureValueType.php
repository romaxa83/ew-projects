<?php

namespace App\GraphQL\Types\Catalog\Products;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Features;
use App\Models\Catalog\Products\ProductFeatureValue;

class FeatureValueType extends BaseType
{
    public const NAME = 'ProductFeatureValueType';
    public const MODEL = ProductFeatureValue::class;

    public function fields(): array
    {
        return [
            'feature' => [
                'type' => Features\Features\FeatureType::type(),
                'is_relation' => true,
            ],
            'value' => [
                'type' => Features\Values\ValueType::type(),
                'is_relation' => true,
            ],
        ];
    }
}

