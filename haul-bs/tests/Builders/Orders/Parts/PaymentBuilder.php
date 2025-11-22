<?php

namespace Tests\Builders\Orders\Parts;

use App\Enums\Orders\Parts\PaymentMethod;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Payment;
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

    public function method(PaymentMethod $value): self
    {
        $this->data['payment_method'] = $value->value;
        return $this;
    }
}
