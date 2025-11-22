<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\GraphQL\Queries\Common\Commercial\BaseCommercialProjectUnitsQuery;

class CommercialProjectUnitsQuery extends BaseCommercialProjectUnitsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}


