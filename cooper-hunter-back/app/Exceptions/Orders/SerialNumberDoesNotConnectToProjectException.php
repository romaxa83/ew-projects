<?php

namespace App\Exceptions\Orders;

use Core\Exceptions\TranslatedException;

class SerialNumberDoesNotConnectToProjectException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.project.forbidden'));
    }
}
