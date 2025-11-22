<?php

namespace Tests\Builders\Orders\Parts;

use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Item;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Shipping;
use Tests\Builders\BaseBuilder;

class ItemBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Item::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function inventory(Inventory $model): self
    {
        $this->data['inventory_id'] = $model->id;
        $this->data['price'] = $model->price_retail;
        return $this;
    }

    public function shipping(Shipping $model): self
    {
        $this->data['shipping_id'] = $model->id;
        return $this;
    }

    public function qty(float $value): self
    {
        $this->data['qty'] = $value;
        return $this;
    }

    public function price(float $value): self
    {
        $this->data['price'] = $value;
        return $this;
    }

    public function discount(float $value): self
    {
        $this->data['discount'] = $value;
        return $this;
    }

    public function price_old(float $value): self
    {
        $this->data['price_old'] = $value;
        return $this;
    }

    public function delivery_cost(float $value): self
    {
        $this->data['delivery_cost'] = $value;
        return $this;
    }

    public function free_shipping(bool $value): self
    {
        $this->data['free_shipping'] = $value;
        return $this;
    }
}
