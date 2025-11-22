<?php

namespace App\Services\Order\Exceptions;

use App\Exceptions\ErrorsCode;
use Exception;

class OrderFreeTimeException extends Exception
{
    public static function serviceNotSupport()
    {
        throw new self(__('error.order.free time.not support this service'),ErrorsCode::BAD_REQUEST);
    }

    public static function notHaveSchedule()
    {
        throw new self(__('error.order.free time.not have schedule'),ErrorsCode::BAD_REQUEST);
    }
}
