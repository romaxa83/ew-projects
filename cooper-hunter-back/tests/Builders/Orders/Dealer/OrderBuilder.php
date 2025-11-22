<?php

namespace Tests\Builders\Orders\Dealer;

use App\Models\Companies\ShippingAddress;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Tests\Builders\BaseBuilder;

class OrderBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order::class;
    }

    public function setStatus(string $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    public function setDealer(Dealer $model): self
    {
        $this->data['dealer_id'] = $model->id;
        return $this;
    }

    public function setShippingAddress(ShippingAddress $model): self
    {
        $this->data['shipping_address_id'] = $model->id;
        return $this;
    }

    public function withAllPrices(): self
    {
        $this->data['tax'] = 20.5;
        $this->data['shipping_price'] = 10.5;
        $this->data['total'] = 1000.5;
        $this->data['total_discount'] = 29.5;
        $this->data['total_with_discount'] = 1001.5;

        return $this;
    }
}
