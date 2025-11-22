<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\Helpers;
use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string Delivery()
 * @method static string Pickup()
 */
enum DeliveryType: string {

    use InvokableCases;
    use RuleIn;
    use Label;
    use Helpers;

    case Delivery = 'delivery';
    case Pickup = 'pickup';

    public function isPickup(): bool
    {
        return $this === self::Pickup;
    }

    public function isDelivery(): bool
    {
        return $this === self::Delivery;
    }
}
