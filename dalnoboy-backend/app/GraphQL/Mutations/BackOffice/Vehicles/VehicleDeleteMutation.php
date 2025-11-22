<?php


namespace App\GraphQL\Mutations\BackOffice\Vehicles;


use App\GraphQL\Types\NonNullType;
use App\Models\Vehicles\Vehicle;
use App\Permissions\Vehicles\VehicleDeletePermission;
use App\Services\Vehicles\VehicleService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VehicleDeleteMutation extends BaseMutation
{
    public const NAME = 'vehicleDelete';
    public const PERMISSION = VehicleDeletePermission::KEY;

    public function __construct(private VehicleService $service)
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
                    Rule::exists(Vehicle::class, 'id')
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
            fn () => $this->service->delete(
                Vehicle::find($args['id'])
            )
        );
    }
}
