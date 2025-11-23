<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class PayrollBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Payroll\Payroll::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }
}
