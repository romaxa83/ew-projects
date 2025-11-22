<?php

namespace App\GraphQL\Mutations\Common\Dictionaries\VehicleMakes;

use App\Dto\Dictionaries\VehicleMakeDto;
use App\GraphQL\InputTypes\Dictionaries\VehicleMakeInputType;
use App\GraphQL\Types\Dictionaries\VehicleMakeType;
use App\Models\Dictionaries\VehicleMake;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\VehicleMakeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseVehicleMakeCreateMutation extends BaseMutation
{
    public const NAME = 'vehicleMakeCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(protected VehicleMakeService $service)
    {
        $this->setGuard();
    }

    abstract protected function setGuard(): void;

    public function type(): Type
    {
        return VehicleMakeType::nonNullType();
    }

    public function args(): array
    {
        return [
            'vehicle_make' => [
                'type' => VehicleMakeInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return VehicleMake
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): VehicleMake
    {
        return makeTransaction(
            fn() => $this->service->create(
                VehicleMakeDto::byArgs($args['vehicle_make']),
                $this->user()
            )
        );
    }
}
