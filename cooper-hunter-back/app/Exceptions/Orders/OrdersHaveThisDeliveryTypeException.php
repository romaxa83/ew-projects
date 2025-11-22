<?php

namespace App\Exceptions\Orders;

use Core\Exceptions\TranslatedException;

class OrdersHaveThisDeliveryTypeException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.order.orders_have_this_delivery_type'));
    }
}
