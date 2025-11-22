<?php


namespace App\Exceptions\Order;

use Exception;

class OrderHasNotHadInspectionYet extends Exception
{

    public function __construct(string $location)
    {
        parent::__construct(trans("The order has not had " . $location . " inspection yet."));
    }
}
