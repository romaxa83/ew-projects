<?php

namespace App\Events\Events\Customers;

use App\Models\Customers\Customer;

class CustomerGiveEcommTag
{
    public function __construct(
        protected Customer $model
    )
    {}

    public function getModel(): Customer
    {
        return $this->model;
    }
}
