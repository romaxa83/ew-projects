<?php

namespace App\GraphQL\Queries\BackOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseDictionariesQuery;

class DictionariesQuery extends BaseDictionariesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
