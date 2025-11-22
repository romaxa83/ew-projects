<?php

namespace App\Events\Events\Inventories\Inventories;

use App\Models\Inventories\Inventory;

class ChangeQuantityInventory
{
    // добавлен флаг для того если нужно вызвать данный ивент, но не отправлять данные в ecom
    protected bool $sendToEcomm = true;

    public function __construct(
        protected Inventory $model,
        protected ?float $oldQuantity = null
    )
    {}

    public function getModel(): Inventory
    {
        return $this->model;
    }

    public function sendToEcomm():bool
    {
        return $this->sendToEcomm;
    }

    public function setSendToEcomm(bool $value): self
    {
        $this->sendToEcomm = $value;

        return $this;
    }
}
