<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders;

use App\GraphQL\Mutations\Common\Orders\BaseOrderConnectProjectMutation;

class OrderConnectProjectMutation extends BaseOrderConnectProjectMutation
{
    protected function setMutationGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
