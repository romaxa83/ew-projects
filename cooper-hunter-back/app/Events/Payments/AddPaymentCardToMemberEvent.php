<?php

namespace App\Events\Payments;

use App\Dto\Payments\PaymentCardDto;
use App\Models\Payments\PaymentCard;

class AddPaymentCardToMemberEvent
{
    public function __construct(
        protected PaymentCard $model,
        protected PaymentCardDto $dto
    )
    {}

    public function getPaymentCard(): PaymentCard
    {
        return $this->model;
    }

    public function getDto(): PaymentCardDto
    {
        return $this->dto;
    }
}
