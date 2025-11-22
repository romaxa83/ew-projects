<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireMakesQuery;

class TireMakesQuery extends BaseTireMakesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
