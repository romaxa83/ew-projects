<?php


namespace App\GraphQL\Mutations\BackOffice\Clients;


use App\GraphQL\Mutations\Common\Clients\BaseClientUpdateMutation;

class ClientUpdateMutation extends BaseClientUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
