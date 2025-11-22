<?php


namespace App\GraphQL\Queries\BackOffice\Vehicles\Schemas;


use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Vehicles\Schemas\SchemaVehicleType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Permissions\Vehicles\Schemas\VehicleSchemaShowPermission;
use App\Services\Vehicles\Schemas\SchemaVehicleService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class SchemaVehicleDefaultQuery extends BaseQuery
{
    public const NAME = 'schemaVehicleDefault';
    public const PERMISSION = VehicleSchemaShowPermission::KEY;
    public const DESCRIPTION = 'Getting default vehicle schema to creating new schema';

    public function __construct(private SchemaVehicleService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'vehicle_form' => [
                'type' => VehicleFormEnumType::nonNullType()
            ],
            'axles_count' => [
                'type' => NonNullType::int(),
                'defaultValue' => 5,
                'description' => 'Adding new axles to TRACK form of vehicle. Min 5. Work only with TRAILER form',
                'rules' => [
                    'required',
                    'int',
                    'min:4'
                ]
            ]
        ];
    }

    public function type(): Type
    {
        return SchemaVehicleType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): SchemaVehicle {
        return $this->service->getDefaultSchema($args['vehicle_form'], $args['axles_count']);
    }
}
