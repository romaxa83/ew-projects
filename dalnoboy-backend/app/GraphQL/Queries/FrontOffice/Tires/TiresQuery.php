<?php

namespace App\GraphQL\Queries\FrontOffice\Tires;

use App\GraphQL\Queries\Common\Tires\BaseTiresQuery;

class TiresQuery extends BaseTiresQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
