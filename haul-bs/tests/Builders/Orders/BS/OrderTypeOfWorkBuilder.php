<?php

namespace Tests\Builders\Orders\BS;

use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWork;
use Tests\Builders\BaseBuilder;

class OrderTypeOfWorkBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return TypeOfWork::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }
}
