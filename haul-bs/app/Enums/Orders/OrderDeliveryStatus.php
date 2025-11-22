<?php

namespace App\Enums\Orders;

enum OrderDeliveryStatus: string {
    case New = "new";
    case Sent = "sent";
    case Transported = "transported";
    case Delivered = "delivered";
}


