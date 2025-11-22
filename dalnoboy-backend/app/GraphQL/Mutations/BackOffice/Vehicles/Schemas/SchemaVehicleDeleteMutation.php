<?php


namespace App\GraphQL\Mutations\BackOffice\Vehicles\Schemas;


use App\GraphQL\Types\NonNullType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Permissions\Vehicles\Schemas\VehicleSchemaDeletePermission;
use App\Services\Vehicles\Schemas\SchemaVehicleService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SchemaVehicleDeleteMutation extends BaseMutation
{
    public const NAME = 'schemaVehicleDelete';
    public const PERMISSION = VehicleSchemaDeletePermission::KEY;

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
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                SchemaVehicle::find($args['id'])
            )
        );
    }
}
