<?php


namespace App\GraphQL\Queries\FrontOffice\Orders\Categories;


use App\GraphQL\Queries\Common\Orders\Categories\BaseOrderCategoriesQuery;

class OrderCategoriesQuery extends BaseOrderCategoriesQuery
{

    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
