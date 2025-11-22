<?php

namespace Tests\Builders\Orders\Dealer;

use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use Tests\Builders\BaseBuilder;

class PackingSlipBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return PackingSlip::class;
    }

    public function setOrder(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }
}
