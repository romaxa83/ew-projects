<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\Models\Dictionaries\VehicleClass;

class VehicleClassType extends BaseDictionaryType
{
    public const NAME = 'VehicleClassType';
    public const MODEL = VehicleClass::class;

    protected string $translateTypeClass = VehicleClassTranslateType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'vehicle_form' => [
                    'type' => VehicleFormEnumType::nonNullType(),
                ],
            ]
        );
    }
}
