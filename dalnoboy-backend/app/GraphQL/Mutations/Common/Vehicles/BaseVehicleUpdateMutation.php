<?php


namespace App\GraphQL\Mutations\Common\Vehicles;


use App\Dto\Vehicles\VehicleDto;
use App\GraphQL\InputTypes\Vehicles\VehicleInputType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Vehicles\VehicleType;
use App\Models\Vehicles\Vehicle;
use App\Permissions\Vehicles\VehicleUpdatePermission;
use App\Services\Vehicles\VehicleService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseVehicleUpdateMutation extends BaseMutation
{
    public const NAME = 'vehicleUpdate';
    public const PERMISSION = VehicleUpdatePermission::KEY;

    public function __construct(private VehicleService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

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
            'vehicle' => [
                'type' => VehicleInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return VehicleType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Vehicle
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Vehicle {
        return makeTransaction(
            fn() => $this->service->update(
                VehicleDto::byArgs($args['vehicle']),
                Vehicle::find($args['id'])
            )
        );
    }
}
