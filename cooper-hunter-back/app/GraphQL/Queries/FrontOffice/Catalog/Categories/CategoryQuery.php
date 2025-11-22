<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoryQuery;

class CategoryQuery extends BaseCategoryQuery
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

    protected function getActive(): ?bool
    {
        return true;
    }
}
