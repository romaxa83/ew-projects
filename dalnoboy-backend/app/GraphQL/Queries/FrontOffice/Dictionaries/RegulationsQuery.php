<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseRegulationsQuery;

class RegulationsQuery extends BaseRegulationsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
