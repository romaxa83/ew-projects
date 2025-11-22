<?php

namespace App\Events\Events\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;

class CreateCustomerTaxExemptionEComEvent
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
