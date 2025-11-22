<?php

namespace App\Enums\Orders;

enum OrderDeliveryDriver: string {
    case Fedex = 'fedex';
    case Ups = 'ups';
    case Usps = 'usps';
}


