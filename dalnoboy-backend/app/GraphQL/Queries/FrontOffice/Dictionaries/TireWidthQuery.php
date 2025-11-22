<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireWidthQuery;

class TireWidthQuery extends BaseTireWidthQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
