<?php


namespace App\GraphQL\Mutations\FrontOffice\Drivers;


use App\GraphQL\Mutations\Common\Drivers\BaseDriverUpdateMutation;

class DriverUpdateMutation extends BaseDriverUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setUserGuard();
    }
}
