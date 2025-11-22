<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoryQuery;

class CategoryQuery extends BaseCategoryQuery
{
    public function __construct()
    {
        $this->setAdminGuard();
    }
}
