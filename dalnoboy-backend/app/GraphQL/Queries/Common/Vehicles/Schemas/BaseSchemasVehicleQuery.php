<?php


namespace App\GraphQL\Queries\Common\Vehicles\Schemas;


use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\Vehicles\Schemas\SchemaVehicleType;
use App\Permissions\Vehicles\Schemas\VehicleSchemaShowPermission;
use App\Services\Vehicles\Schemas\SchemaVehicleService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseSchemasVehicleQuery extends BaseQuery
{
    public const NAME = 'schemasVehicle';
    public const PERMISSION = VehicleSchemaShowPermission::KEY;

    public function __construct(private SchemaVehicleService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'vehicle_form' => [
                'type' => VehicleFormEnumType::type()
            ],
            'name' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function type(): Type
    {
        return SchemaVehicleType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->show($args);
    }
}
