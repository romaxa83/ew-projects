<?php

namespace App\GraphQL\Queries\BackOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseVehicleMakesQuery;
use GraphQL\Type\Definition\Type;

class VehicleMakesQuery extends BaseVehicleMakesQuery
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
                'is_moderated' => [
                    'type' => Type::boolean(),
                ],
            ]
        );
    }
}
