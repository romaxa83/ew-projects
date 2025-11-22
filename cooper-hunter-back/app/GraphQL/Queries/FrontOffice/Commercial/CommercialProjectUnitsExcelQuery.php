<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\Common\Commercial\BaseCommercialProjectUnitsExcelQuery;
use Core\Traits\Auth\TechnicianCommercial;

class CommercialProjectUnitsExcelQuery extends BaseCommercialProjectUnitsExcelQuery
{
    use TechnicianCommercial;

    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
