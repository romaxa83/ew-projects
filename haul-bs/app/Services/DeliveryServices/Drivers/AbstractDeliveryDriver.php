<?php

namespace App\Services\DeliveryServices\Drivers;

use App\Models\Orders\Parts\Delivery;

abstract class AbstractDeliveryDriver implements DeliveryDriver
{
    public function __construct(protected Delivery $delivery)
    {
    }
}
