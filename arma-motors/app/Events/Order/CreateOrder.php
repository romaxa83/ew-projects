<?php

namespace App\Events\Order;

use App\Models\Order\Order;
use Illuminate\Queue\SerializesModels;

class CreateOrder
{
    use SerializesModels;

    public function __construct(
        public Order $order,
    )
    {}
}

