<?php

namespace Tests\Builders\Orders\BS;

use App\Models\Inventories\Inventory;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Orders\BS\TypeOfWorkInventory;
use Tests\Builders\BaseBuilder;

class OrderTypeOfWorkInventoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return TypeOfWorkInventory::class;
    }

    public function inventory(Inventory $model): self
    {
        $this->data['inventory_id'] = $model->id;
        return $this;
    }

    public function qty(float $value): self
    {
        $this->data['quantity'] = $value;
        return $this;
    }

    public function price(float $value): self
    {
        $this->data['price'] = $value;
        return $this;
    }

    public function type_of_work(TypeOfWork $model): self
    {
        $this->data['type_of_work_id'] = $model->id;
        return $this;
    }
}
