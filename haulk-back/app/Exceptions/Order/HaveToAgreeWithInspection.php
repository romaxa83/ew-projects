<?php


namespace App\Exceptions\Order;

use Exception;

class HaveToAgreeWithInspection extends Exception
{

    public function __construct(string $location)
    {
        parent::__construct(trans("You have to agree with the " . $location . " inspection."));
    }
}
