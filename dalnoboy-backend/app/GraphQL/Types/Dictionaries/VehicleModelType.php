<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\VehicleModel;

class VehicleModelType extends BaseType
{
    public const NAME = 'VehicleModelType';
    public const MODEL = VehicleModel::class;

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
                'vehicle_make' => [
                    'type' => VehicleMakeType::type(),
                    'is_relation' => true,
                    'alias' => 'vehicleMake',
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
