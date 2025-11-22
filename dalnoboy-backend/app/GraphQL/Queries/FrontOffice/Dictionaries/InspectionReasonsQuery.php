<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseInspectionReasonsQuery;

class InspectionReasonsQuery extends BaseInspectionReasonsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
