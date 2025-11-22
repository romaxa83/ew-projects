<?php


namespace App\GraphQL\Mutations\BackOffice\Drivers;


use App\GraphQL\Mutations\Common\Drivers\BaseDriverCreateMutation;

class DriverCreateMutation extends BaseDriverCreateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
