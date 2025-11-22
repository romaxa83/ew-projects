<?php

namespace App\GraphQL\Queries\FrontOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseDictionariesQuery;

class DictionariesQuery extends BaseDictionariesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
