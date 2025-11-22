<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoriesForSelectQuery;
use App\Permissions\Catalog\Categories\ListPermission;

class CategoriesForSelectQuery extends BaseCategoriesForSelectQuery
{
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }
}
