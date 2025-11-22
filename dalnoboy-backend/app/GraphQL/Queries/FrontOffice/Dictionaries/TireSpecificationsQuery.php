<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireSpecificationsQuery;

class TireSpecificationsQuery extends BaseTireSpecificationsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
