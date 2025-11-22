<?php

namespace App\Enums\Customers;

use App\Foundations\Enums\Traits\InvokableCases;

/**
 * @method static static Delivery()
 */

enum AddressType: string {

    use InvokableCases;

    case Delivery = "delivery";
}
