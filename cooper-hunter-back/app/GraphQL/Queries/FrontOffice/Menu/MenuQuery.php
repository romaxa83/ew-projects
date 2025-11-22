<?php

namespace App\GraphQL\Queries\FrontOffice\Menu;

use App\GraphQL\Queries\Common\Menu\BaseMenuQuery;
use Illuminate\Validation\Rules\Exists;

class MenuQuery extends BaseMenuQuery
{
    protected function setQueryGuard(): void
    {
    }

    protected function idRuleExists(): Exists
    {
        return parent::idRuleExists()
            ->where('active', true);
    }
}
