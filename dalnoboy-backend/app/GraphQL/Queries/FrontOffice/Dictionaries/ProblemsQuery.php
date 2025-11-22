<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseProblemsQuery;

class ProblemsQuery extends BaseProblemsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
