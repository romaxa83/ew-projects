<?php

namespace App\GraphQL\Queries\BackOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleTypesQuery;
use GraphQL\Type\Definition\Type;

class VehicleTypesQuery extends BaseVehicleTypesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'active' => [
                    'type' => Type::boolean(),
                ],
            ]
        );
    }
}
