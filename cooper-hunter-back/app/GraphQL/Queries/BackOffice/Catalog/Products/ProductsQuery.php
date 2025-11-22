<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Products;

use App\GraphQL\Queries\Common\Catalog\Products\BaseProductsQuery;
use App\Permissions\Catalog\Products\ListPermission;

class ProductsQuery extends BaseProductsQuery
{
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }
}
