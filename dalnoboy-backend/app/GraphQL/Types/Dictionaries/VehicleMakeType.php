<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\VehicleMake;

class VehicleMakeType extends BaseType
{
    public const NAME = 'VehicleMakeType';
    public const MODEL = VehicleMake::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
                'vehicle_form' => [
                    'type' => VehicleFormEnumType::type(),
                ],
            ]
        );
    }
}
