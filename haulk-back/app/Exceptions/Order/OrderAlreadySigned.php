<?php


namespace App\Exceptions\Order;

use Exception;

class OrderAlreadySigned extends Exception
{

    public function __construct(string $location)
    {
        parent::__construct(trans("The customer's signature at the " .$location. " location already exists."));
    }
}
