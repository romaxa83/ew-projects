<?php

namespace App\GraphQL\Queries\FrontOffice\Locations;

use App\GraphQL\Queries\Common\Locations\BaseStateQuery;

class States extends BaseStateQuery
{
    protected function initArgs(array $args): array
    {
        $args['status'] = true;

        return $args;
    }
}
