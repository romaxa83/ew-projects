<?php

namespace App\Exceptions\Order;

use Exception;

class NotVinInspectionException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans('Vehicle vin inspection not finished.'));
    }
}
