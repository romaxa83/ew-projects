<?php

namespace App\Exceptions\Orders;

use Core\Exceptions\TranslatedException;

class OrderCantPaidException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.order.order_cant_paid'));
    }
}
