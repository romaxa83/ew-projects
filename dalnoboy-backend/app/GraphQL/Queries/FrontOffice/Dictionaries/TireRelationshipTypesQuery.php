<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireRelationshipTypesQuery;

class TireRelationshipTypesQuery extends BaseTireRelationshipTypesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
