<?php

namespace App\Exceptions\Payment;

use Core\Exceptions\TranslatedException;

class PayPalOrderApprovedException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.payment.order_approved'));
    }
}
