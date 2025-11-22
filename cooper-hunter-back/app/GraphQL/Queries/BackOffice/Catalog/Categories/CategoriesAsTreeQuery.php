<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoriesAsTreeQuery;

class CategoriesAsTreeQuery extends BaseCategoriesAsTreeQuery
{
    public function __construct()
    {
        $this->setAdminGuard();
    }
}
