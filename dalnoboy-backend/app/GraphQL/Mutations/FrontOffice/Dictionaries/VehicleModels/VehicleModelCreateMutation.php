<?php

namespace App\GraphQL\Mutations\FrontOffice\Dictionaries\VehicleModels;

use App\Dto\Dictionaries\VehicleModelDto;
use App\Exceptions\Dictionaries\NotUniqVehicleModelException;
use App\GraphQL\Mutations\Common\Dictionaries\VehicleModels\BaseVehicleModelCreateMutation;
use App\Models\Dictionaries\VehicleModel;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VehicleModelCreateMutation extends BaseVehicleModelCreateMutation
{
    protected function setGuard(): void
    {
        $this->setUserGuard();
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
        try {
            return makeTransaction(
                fn() => $this->service->create(
                    VehicleModelDto::byArgs($args['vehicle_model']),
                    $this->user()
                )
            );
        } catch (NotUniqVehicleModelException $exception) {
            if ($exception->getSimilarVehicleModel()->active) {
                throw $exception;
            }

            return makeTransaction(
                fn() => $this->service->update(
                    VehicleModelDto::byArgs($args['vehicle_model']),
                    $exception->getSimilarVehicleModel()
                )
            );
        }
    }
}
