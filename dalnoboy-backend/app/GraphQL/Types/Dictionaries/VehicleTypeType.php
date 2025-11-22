<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Models\Dictionaries\VehicleType;
use GraphQL\Type\Definition\Type;

class VehicleTypeType extends BaseDictionaryType
{
    public const NAME = 'VehicleTypeType';
    public const MODEL = VehicleType::class;

    protected string $translateTypeClass = VehicleTypeTranslateType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'vehicle_classes' => [
                    'type' => Type::listOf(VehicleClassType::nonNullType()),
                    'is_relation' => true,
                    'alias' => 'vehicleClasses',
                ],
            ]
        );
    }
}
