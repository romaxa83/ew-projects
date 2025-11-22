<?php

namespace App\Events\Events\Customers;

use App\Models\Customers\CustomerTaxExemption;

class DeclineCustomerTaxExemptionEvent
{
    public function __construct(
        protected CustomerTaxExemption $model
    )
    {}

    public function getModel(): CustomerTaxExemption
    {
        return $this->model;
    }
}
