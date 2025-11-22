<?php

namespace Tests\Builders\Orders\BS;

use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;
use Tests\Builders\BaseBuilder;

class PaymentBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Payment::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function amount(float $value): self
    {
        $this->data['amount'] = $value;
        return $this;
    }
}

