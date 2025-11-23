<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use App\Models\Order\Inventory;
use Tests\Builders\BaseBuilder;

class InventoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Inventory::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function is_section(int $value): self
    {
        $this->data['is_section'] = $value;
        return $this;
    }

    public function sort(int $value): self
    {
        $this->data['sort'] = $value;
        return $this;
    }

    public function section_id(?Inventory $model): self
    {
        if($model instanceof Inventory){
            $this->data['section_id'] = $model->id;
        } else {
            $this->data['section_id'] = 0;
        }

        return $this;
    }
}
