<?php

namespace App\Events\Orders\Dealer;

use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovedOrderEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        protected Order $model,
        protected bool $changed,
    )
    {}

    public function getOrder(): Order
    {
        return $this->model;
    }

    public function isChanged(): bool
    {
        return $this->changed;
    }
}
