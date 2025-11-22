<?php

namespace App\Exceptions\Payment;

use Core\Exceptions\TranslatedException;

class PayPalSomethingWentWrongException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.payment.something_went_wrong'));
    }
}
