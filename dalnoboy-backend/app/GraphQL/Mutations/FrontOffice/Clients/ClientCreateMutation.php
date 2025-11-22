<?php


namespace App\GraphQL\Mutations\FrontOffice\Clients;


use App\GraphQL\Mutations\Common\Clients\BaseClientCreateMutation;

class ClientCreateMutation extends BaseClientCreateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setUserGuard();
    }
}
