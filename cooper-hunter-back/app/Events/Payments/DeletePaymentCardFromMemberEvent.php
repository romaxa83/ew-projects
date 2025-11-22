<?php

namespace App\Events\Payments;

use App\Models\Payments\PaymentCard;

class DeletePaymentCardFromMemberEvent
{
    public function __construct(
        protected PaymentCard $model,
    )
    {}

    public function getPaymentCard(): PaymentCard
    {
        return $this->model;
    }
}
