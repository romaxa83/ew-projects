<?php

namespace App\Exceptions\Orders;

use Core\Exceptions\TranslatedException;

class OrderNotFoundException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.order.orders_not_found'));
    }
}
