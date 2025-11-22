<?php

namespace App\GraphQL\Queries\FrontOffice\Content\OurCases;

use App\GraphQL\Queries\Common\Content\OurCase\BaseOurCaseQuery;

class OurCasesQuery extends BaseOurCaseQuery
{
    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
