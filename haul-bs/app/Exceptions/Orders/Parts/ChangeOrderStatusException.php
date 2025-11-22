<?php

namespace App\Exceptions\Orders\Parts;

use Exception;
use Illuminate\Http\Response;

class ChangeOrderStatusException extends Exception
{
    public function __construct(
        ?string $msg = null,
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR
    )
    {
        parent::__construct($msg ?? __('exceptions.orders.status_cant_be_change'), $code);
    }
}
