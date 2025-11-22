<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Products;

use App\GraphQL\Queries\Common\Catalog\Products\BaseProductQuery;
use App\Permissions\Catalog\Products\ListPermission;

class ProductQuery extends BaseProductQuery
{
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }
}
