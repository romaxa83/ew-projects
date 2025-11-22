<?php


namespace App\GraphQL\Queries\BackOffice\Vehicles;


use App\GraphQL\Queries\Common\Vehicles\BaseVehiclesQuery;
use GraphQL\Type\Definition\Type;

class VehiclesQuery extends BaseVehiclesQuery
{
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'is_moderated' => [
                    'type' => Type::boolean()
                ]
            ]
        );
    }

    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
