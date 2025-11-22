<?php


namespace App\GraphQL\Queries\FrontOffice\Orders\DeliveryTypes;


use App\GraphQL\Queries\Common\Orders\DeliveryTypes\BaseOrderDeliveryTypesQuery;

class OrderDeliveryTypesQuery extends BaseOrderDeliveryTypesQuery
{

    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
