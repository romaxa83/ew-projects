<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoriesAsTreeQuery;

class CategoriesAsTreeQuery extends BaseCategoriesAsTreeQuery
{
    public function __construct()
    {
        $this->setMemberGuard();
    }

    protected function getActive(): ?bool
    {
        return true;
    }
}
