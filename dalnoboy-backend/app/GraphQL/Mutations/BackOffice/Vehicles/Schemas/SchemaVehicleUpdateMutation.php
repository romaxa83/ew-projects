<?php


namespace App\GraphQL\Mutations\BackOffice\Vehicles\Schemas;


use App\Dto\Vehicles\SchemaVehicleDto;
use App\GraphQL\InputTypes\Vehicles\Schemas\SchemaVehicleInputType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Vehicles\Schemas\SchemaVehicleType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Permissions\Vehicles\Schemas\VehicleSchemaUpdatePermission;
use App\Services\Vehicles\Schemas\SchemaVehicleService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SchemaVehicleUpdateMutation extends BaseMutation
{
    public const NAME = 'schemaVehicleUpdate';
    public const PERMISSION = VehicleSchemaUpdatePermission::KEY;

    public function __construct(private SchemaVehicleService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(SchemaVehicle::class)
                        ->where('is_default', false)
                ]
            ],
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
            fn() => $this->service->update(
                SchemaVehicleDto::byArgs($args['schema']),
                SchemaVehicle::find($args['id'])
            )
        );
    }
}
