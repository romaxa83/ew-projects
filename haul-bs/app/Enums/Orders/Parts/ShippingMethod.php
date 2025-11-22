<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\RuleIn;

enum ShippingMethod: string {

    use InvokableCases;
    use RuleIn;

    case Pickup = 'Pickup';
    case UPS_Standard = 'UPS Standard';
    case UPS_Next_Day_Air_Saver = 'UPS Next Day Air Saver';
    case UPS_Next_Day_Air = 'UPS Next Day Air';
    case FedEx_Ground = 'FedEx Ground';
    case FedEx_Express_Saver = 'FedEx Express Saver';
}
