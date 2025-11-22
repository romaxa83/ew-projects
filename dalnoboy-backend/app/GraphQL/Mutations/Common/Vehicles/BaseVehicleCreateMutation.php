<?php


namespace App\GraphQL\Mutations\Common\Vehicles;


use App\Dto\Vehicles\VehicleDto;
use App\GraphQL\InputTypes\Vehicles\VehicleInputType;
use App\GraphQL\Types\Vehicles\VehicleType;
use App\Models\Vehicles\Vehicle;
use App\Permissions\Vehicles\VehicleCreatePermission;
use App\Services\Vehicles\VehicleService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseVehicleCreateMutation extends BaseMutation
{
    public const NAME = 'vehicleCreate';
    public const PERMISSION = VehicleCreatePermission::KEY;

    public function __construct(private VehicleService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

    public function args(): array
    {
        return [
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
            fn() => $this->service->create(
                VehicleDto::byArgs($args['vehicle'])
            )
        );
    }
}
