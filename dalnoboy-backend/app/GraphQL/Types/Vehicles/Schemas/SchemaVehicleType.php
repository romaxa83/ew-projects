<?php


namespace App\GraphQL\Types\Vehicles\Schemas;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Services\Vehicles\Schemas\SchemaVehicleService;

class SchemaVehicleType extends BaseType
{
    public const NAME = 'SchemaVehicleType';
    public const MODEL = SchemaVehicle::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'vehicle_form' => [
                    'type' => VehicleFormEnumType::nonNullType()
                ],
                'image' => [
                    'type' => NonNullType::string(),
                    'description' => 'Schema image in base64 format',
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(SchemaVehicle $schemaVehicle) => resolve(SchemaVehicleService::class)->renderSchema(
                        $schemaVehicle
                    )
                ],
                'axles' => [
                    'type' => SchemaAxleType::nonNullList(),
                    'is_relation' => true
                ]
            ]
        );
    }
}
