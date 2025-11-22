<?php

namespace Tests\Builders\TypeOfWorks;

use App\Models\Inventories\Inventory;
use App\Models\TypeOfWorks\TypeOfWork;
use App\Models\TypeOfWorks\TypeOfWorkInventory;
use Tests\Builders\BaseBuilder;

class TypeOfWorkInventoryBuilder extends BaseBuilder
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

    public function work(TypeOfWork $model): self
    {
        $this->data['type_of_work_id'] = $model->id;
        return $this;
    }
}
