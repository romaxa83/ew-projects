<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Products;

use App\GraphQL\Queries\Common\Catalog\Products\BaseProductsQuery;

class ProductsQuery extends BaseProductsQuery
{
    public function __construct()
    {
        $this->setMemberGuard();
    }

    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
