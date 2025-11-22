<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\NonNullType;

class VehicleMakeInputType extends BaseInputType
{
    public const NAME = 'VehicleMakeInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only true - field does not depend on value',
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'is_moderated' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only false - field does not depend on value',
            ],
            'is_offline' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => false,
                'description' => 'For FrontOffice only',
            ],
            'vehicle_form' => [
                'type' => VehicleFormEnumType::type(),
            ],
        ];
    }
}
