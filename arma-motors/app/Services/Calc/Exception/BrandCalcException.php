<?php

namespace App\Services\Calc\Exception;

use Exception;

class BrandCalcException extends Exception
{
    public static function throwWrongType()
    {
        throw new self(__('exception.brand-calc.wrong type'));
    }
}
