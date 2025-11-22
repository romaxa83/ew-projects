<?php

namespace App\GraphQL\Mutations\Common\Dictionaries\VehicleModels;

use App\Dto\Dictionaries\VehicleModelDto;
use App\GraphQL\InputTypes\Dictionaries\VehicleModelInputType;
use App\GraphQL\Types\Dictionaries\VehicleModelType;
use App\Models\Dictionaries\VehicleModel;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\VehicleModelService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseVehicleModelCreateMutation extends BaseMutation
{
    public const NAME = 'vehicleModelCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

    public function __construct(protected VehicleModelService $service)
    {
        $this->setGuard();
    }

    abstract protected function setGuard(): void;

    public function type(): Type
    {
        return VehicleModelType::nonNullType();
    }

    public function args(): array
    {
        return [
            'vehicle_model' => [
                'type' => VehicleModelInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return VehicleModel
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): VehicleModel
    {
        return makeTransaction(
            fn() => $this->service->create(
                VehicleModelDto::byArgs($args['vehicle_model']),
                $this->user()
            )
        );
    }
}
