<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;

class VehicleClassInputType extends BaseDictionaryInputType
{
    public const NAME = 'VehicleClassInputType';

    protected string $translateInputTypeClass = VehicleClassTranslateInputType::class;

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
