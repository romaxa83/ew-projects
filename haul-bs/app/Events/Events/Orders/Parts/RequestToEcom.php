<?php

namespace App\Events\Events\Orders\Parts;

use App\Models\Orders\Parts\Order;

class RequestToEcom
{
    public function __construct(
        protected Order $model,
        protected string $action,
    )
    {}

    public function getModel(): Order
    {
        return $this->model;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}

