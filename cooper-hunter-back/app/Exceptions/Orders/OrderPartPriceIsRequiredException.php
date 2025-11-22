<?php

namespace App\Exceptions\Orders;

use Core\Exceptions\TranslatedException;

class OrderPartPriceIsRequiredException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.order.order_part_price_is_required'));
    }
}
