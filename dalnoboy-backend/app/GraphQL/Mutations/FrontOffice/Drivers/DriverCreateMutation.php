<?php


namespace App\GraphQL\Mutations\FrontOffice\Drivers;


use App\GraphQL\Mutations\Common\Drivers\BaseDriverCreateMutation;

class DriverCreateMutation extends BaseDriverCreateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setUserGuard();
    }
}
