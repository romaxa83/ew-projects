<?php

namespace App\Services\DeliveryServices\Drivers;

use App\Enums\Orders\Parts\DeliveryStatus;

interface DeliveryDriver
{
    public function getStatusTracking(): string;
    public function mapToOrderDeliveryStatus(): DeliveryStatus;
}
