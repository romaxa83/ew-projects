<?php

namespace App\Exceptions\Orders;

use Core\Exceptions\TranslatedException;

class OrderCategoryUsedException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.order.order_category_used'));
    }
}
