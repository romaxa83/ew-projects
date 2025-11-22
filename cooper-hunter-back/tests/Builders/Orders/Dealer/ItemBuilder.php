<?php

namespace Tests\Builders\Orders\Dealer;

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\Order;
use Tests\Builders\BaseBuilder;

class ItemBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Item::class;
    }

    public function setOrder(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function setProduct(Product $model): self
    {
        $this->data['product_id'] = $model->id;
        return $this;
    }
}
