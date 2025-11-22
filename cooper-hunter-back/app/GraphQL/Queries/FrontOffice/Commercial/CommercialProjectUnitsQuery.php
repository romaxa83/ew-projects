<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\Common\Commercial\BaseCommercialProjectUnitsQuery;
use Core\Traits\Auth\TechnicianCommercial;

class CommercialProjectUnitsQuery extends BaseCommercialProjectUnitsQuery
{
    use TechnicianCommercial;

    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
