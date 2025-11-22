<?php

namespace App\Exceptions\Parser;

use Exception;

class EmptyVehiclesException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans('File doesn\'t have any vehicles.'));
    }
}
