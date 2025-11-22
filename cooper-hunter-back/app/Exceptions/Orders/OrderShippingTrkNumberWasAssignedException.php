<?php

namespace App\Exceptions\Orders;

use Core\Exceptions\TranslatedException;

class OrderShippingTrkNumberWasAssignedException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.order.order_shipping_assigned_trk_number'));
    }
}
