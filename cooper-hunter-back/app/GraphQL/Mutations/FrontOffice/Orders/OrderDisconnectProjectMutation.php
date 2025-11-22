<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders;

use App\GraphQL\Mutations\Common\Orders\BaseOrderDisconnectProjectMutation;

class OrderDisconnectProjectMutation extends BaseOrderDisconnectProjectMutation
{
    protected function setMutationGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
