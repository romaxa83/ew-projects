<?php


namespace App\GraphQL\Queries\FrontOffice\Clients;


use App\GraphQL\Queries\Common\Clients\BaseClientsQuery;

class ClientsQuery extends BaseClientsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
