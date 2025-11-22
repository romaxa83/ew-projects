<?php

namespace App\GraphQL\Types\Vehicles;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Clients\ClientType;
use App\GraphQL\Types\Dictionaries\VehicleClassType;
use App\GraphQL\Types\Dictionaries\VehicleMakeType;
use App\GraphQL\Types\Dictionaries\VehicleModelType;
use App\GraphQL\Types\Dictionaries\VehicleTypeType;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\Inspections\InspectionType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Vehicles\Schemas\SchemaVehicleType;
use App\Models\Vehicles\Vehicle;
use GraphQL\Type\Definition\Type;

class VehicleType extends BaseType
{
    public const NAME = 'VehicleType';
    public const MODEL = Vehicle::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'photo' => [
                    'type' => MediaType::type(),
                    'resolve' => fn(Vehicle $vehicle) => $vehicle->getFirstMedia(Vehicle::MC_VEHICLE),
                    'is_relation' => false,
                    'selectable' => false,
                ],
                'state_number' => [
                    'type' => NonNullType::string(),
                ],
                'state_number_photo' => [
                    'type' => MediaType::type(),
                    'resolve' => fn(Vehicle $vehicle) => $vehicle->getFirstMedia(Vehicle::MC_STATE_NUMBER),
                    'is_relation' => false,
                    'selectable' => false,
                ],
                'vin' => [
                    'type' => Type::string(),
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
                'form' => [
                    'type' => VehicleFormEnumType::nonNullType(),
                ],
                'class' => [
                    'type' => VehicleClassType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'vehicleClass'
                ],
                'type' => [
                    'type' => VehicleTypeType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'vehicleType'
                ],
                'make' => [
                    'type' => VehicleMakeType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'vehicleMake'
                ],
                'model' => [
                    'type' => VehicleModelType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'vehicleModel'
                ],
                'client' => [
                    'type' => ClientType::nonNullType(),
                    'is_relation' => true,
                ],
                'schema' => [
                    'type' => SchemaVehicleType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'schemaVehicle'
                ],
                'odo' => [
                    'type' => Type::int(),
                ],
                'active' => [
                    'type' => NonNullType::boolean()
                ],
                'inspections' => [
                    'type' => InspectionType::list(),
                    'is_relation' => true,
                ],
                'last_inspection' => [
                    'type' => InspectionType::type(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Vehicle $vehicle) => $vehicle->lastInspection()
                ]
            ]
        );
    }
}
