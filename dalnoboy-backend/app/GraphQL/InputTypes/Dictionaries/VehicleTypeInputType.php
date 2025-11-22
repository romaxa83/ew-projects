<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\NonNullType;

class VehicleTypeInputType extends BaseDictionaryInputType
{
    public const NAME = 'VehicleTypeInputType';

    protected string $translateInputTypeClass = VehicleTypeTranslateInputType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'vehicle_classes' => [
                    'type' => NonNullType::listOf(NonNullType::id()),
                ],
            ]
        );
    }
}
