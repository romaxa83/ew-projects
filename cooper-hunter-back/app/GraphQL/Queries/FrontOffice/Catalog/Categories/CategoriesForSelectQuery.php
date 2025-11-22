<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoriesForSelectQuery;

class CategoriesForSelectQuery extends BaseCategoriesForSelectQuery
{
    public function __construct()
    {
        $this->setMemberGuard();
    }
}
