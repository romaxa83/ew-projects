<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class WaypointBuilder extends BaseBuilder
{

    public function modelClass(): string
    {
        return Order\Waypoint::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }
}
