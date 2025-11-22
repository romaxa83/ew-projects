<?php

namespace App\Events\Orders\Dealer;

use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutOrderEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected Order $model)
    {}

    public function getOrder(): Order
    {
        return $this->model;
    }
}
