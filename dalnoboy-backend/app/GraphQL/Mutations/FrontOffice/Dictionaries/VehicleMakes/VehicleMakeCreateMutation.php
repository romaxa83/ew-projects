<?php

namespace App\GraphQL\Mutations\FrontOffice\Dictionaries\VehicleMakes;

use App\Dto\Dictionaries\VehicleMakeDto;
use App\Exceptions\Dictionaries\NotUniqVehicleMakeException;
use App\GraphQL\Mutations\Common\Dictionaries\VehicleMakes\BaseVehicleMakeCreateMutation;
use App\Models\Dictionaries\VehicleMake;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class VehicleMakeCreateMutation extends BaseVehicleMakeCreateMutation
{
    protected function setGuard(): void
    {
        $this->setUserGuard();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): VehicleMake {
        try {
            return makeTransaction(
                fn() => $this->service->create(
                    VehicleMakeDto::byArgs($args['vehicle_make']),
                    $this->user()
                )
            );
        } catch (NotUniqVehicleMakeException $exception) {
            if ($exception->getSimilarVehicleMake()->active) {
                throw $exception;
            }

            return makeTransaction(
                fn() => $this->service->update(
                    VehicleMakeDto::byArgs($args['vehicle_make']),
                    $exception->getSimilarVehicleMake()
                )
            );
        }
    }
}
