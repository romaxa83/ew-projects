<?php


namespace App\Exceptions\Order;

use Exception;

class ChangeOrderStatusException extends Exception
{

    public function __construct()
    {
        parent::__construct(trans("Can't change order status"));
    }
}
