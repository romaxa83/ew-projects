<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use App\User;
use Tests\Builders\BaseBuilder;

class PaymentBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Payment::class;
    }

    public function user(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function in_total_sum(bool $value): self
    {
        $this->data['in_total_sum'] = $value;
        return $this;
    }

    public function amount(float $value): self
    {
        $this->data['amount'] = $value;
        return $this;
    }


}
