<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireSize;

class TireSizeType extends BaseType
{
    public const NAME = 'TireSizeType';
    public const MODEL = TireSize::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'tire_width' => [
                    'type' => TireWidthType::type(),
                    'is_relation' => true,
                    'alias' => 'tireWidth',
                ],
                'tire_height' => [
                    'type' => TireHeightType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'tireHeight',
                ],
                'tire_diameter' => [
                    'type' => TireDiameterType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'tireDiameter',
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
