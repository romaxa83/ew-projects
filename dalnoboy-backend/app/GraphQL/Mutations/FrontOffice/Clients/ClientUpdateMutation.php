<?php


namespace App\GraphQL\Mutations\FrontOffice\Clients;


use App\GraphQL\Mutations\Common\Clients\BaseClientUpdateMutation;

class ClientUpdateMutation extends BaseClientUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setUserGuard();
    }
}
