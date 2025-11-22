<?php

namespace Tests\Builders\Orders\Dealer;

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Orders\Dealer\PackingSlipItem;
use Tests\Builders\BaseBuilder;

class PackingSlipItemBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return PackingSlipItem::class;
    }

    public function setPackingSlip(PackingSlip $model): self
    {
        $this->data['packing_slip_id'] = $model->id;
        return $this;
    }

    public function setProduct(Product $model): self
    {
        $this->data['product_id'] = $model->id;
        return $this;
    }

    public function setOrderItem(Item $model): self
    {
        $this->data['order_item_id'] = $model->id;
        return $this;
    }
}
