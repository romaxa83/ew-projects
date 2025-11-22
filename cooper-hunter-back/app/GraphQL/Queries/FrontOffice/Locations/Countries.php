<?php

namespace App\GraphQL\Queries\FrontOffice\Locations;

use App\GraphQL\Queries\Common\Locations\BaseCountryQuery;

class Countries extends BaseCountryQuery
{
    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
