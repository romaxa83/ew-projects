<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class EstimateCalculatedBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Estimate\Calculated::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function title(string $value): self
    {
        $this->data['title'] = $value;
        return $this;
    }

    public function value(string $value): self
    {
        $this->data['value'] = $value;
        return $this;
    }
}
