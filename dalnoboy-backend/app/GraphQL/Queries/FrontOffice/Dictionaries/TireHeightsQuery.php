<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireHeightsQuery;

class TireHeightsQuery extends BaseTireHeightsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
