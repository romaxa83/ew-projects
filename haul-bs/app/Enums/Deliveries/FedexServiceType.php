<?php

namespace App\Enums\Deliveries;

enum FedexServiceType: string {
    case FEDEX_GROUND = 'FEDEX_GROUND';
    case STANDARD_OVERNIGHT = 'STANDARD_OVERNIGHT';
}
