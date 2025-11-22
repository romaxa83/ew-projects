<?php

namespace App\Events\Events\Inventories\Inventories;

use App\Models\Inventories\Inventory;

class UpdateImageInventoryEvent
{
    public function __construct(
        protected Inventory $model
    )
    {}

    public function getModel(): Inventory
    {
        return $this->model;
    }

}
