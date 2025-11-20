<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DtoException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if(!$message){
            $message = 'В DTO не переданна модель данных';
        }

        parent::__construct($message, $code, $previous);
    }
}
