<?php

namespace Tests\Builders\Payment;

use App\Contracts\Payment\PaymentModel;
use App\Models\Payments\PaymentCard;
use Tests\Builders\BaseBuilder;

class PaymentCardBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return PaymentCard::class;
    }

    public function setMember(PaymentModel $model): self
    {
        $this->data['member_type'] = $model::MORPH_NAME;
        $this->data['member_id'] = $model->id;

        return $this;
    }

    public function default(): self
    {
        $this->data['default'] = true;

        return $this;
    }
}
