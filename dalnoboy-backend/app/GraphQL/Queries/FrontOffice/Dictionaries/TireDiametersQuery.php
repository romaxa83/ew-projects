<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireDiametersQuery;

class TireDiametersQuery extends BaseTireDiametersQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
