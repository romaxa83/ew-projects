<?php

namespace App\Exceptions\Order;

use Exception;

class OrderCantBeMovedOffers extends Exception
{
    public function __construct()
    {
        parent::__construct(trans('This order can\'t be moved to offers.'));
    }
}
