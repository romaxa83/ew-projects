<?php

namespace App\GraphQL\Queries\BackOffice\Companies;

use App\GraphQL\Queries\Common\Companies\BaseShippingAddressListQuery;

class ShippingAddressListQuery extends BaseShippingAddressListQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
