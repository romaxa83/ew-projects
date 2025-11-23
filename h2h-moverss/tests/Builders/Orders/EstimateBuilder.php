<?php

namespace Tests\Builders\Orders;

use App\Enums\Orders\EstimateTypeEnum;
use App\Models\Order;
use Tests\Builders\BaseBuilder;

class EstimateBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Estimate::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function type(EstimateTypeEnum $value): self
    {
        $this->data['type'] = $value->value;
        return $this;
    }
}
