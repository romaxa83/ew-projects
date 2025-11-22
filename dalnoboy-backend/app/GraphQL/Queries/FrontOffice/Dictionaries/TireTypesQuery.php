<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireTypesQuery;

class TireTypesQuery extends BaseTireTypesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
