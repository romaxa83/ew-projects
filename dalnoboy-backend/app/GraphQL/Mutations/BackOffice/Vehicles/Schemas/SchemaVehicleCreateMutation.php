<?php


namespace App\GraphQL\Mutations\BackOffice\Vehicles\Schemas;


use App\Dto\Vehicles\SchemaVehicleDto;
use App\GraphQL\InputTypes\Vehicles\Schemas\SchemaVehicleInputType;
use App\GraphQL\Types\Vehicles\Schemas\SchemaVehicleType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Permissions\Vehicles\Schemas\VehicleSchemaCreatePermission;
use App\Services\Vehicles\Schemas\SchemaVehicleService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SchemaVehicleCreateMutation extends BaseMutation
{
    public const NAME = 'schemaVehicleCreate';
    public const PERMISSION = VehicleSchemaCreatePermission::KEY;

    public function __construct(private SchemaVehicleService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'schema' => [
                'type' => SchemaVehicleInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return SchemaVehicleType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return SchemaVehicle
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): SchemaVehicle {
        return makeTransaction(
            fn() => $this->service->create(
                SchemaVehicleDto::byArgs($args['schema'])
            )
        );
    }
}
