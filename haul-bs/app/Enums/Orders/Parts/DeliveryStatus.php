<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\Helpers;
use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string Sent()
 * @method static string Delivered()
 */
enum DeliveryStatus: string {

    use InvokableCases;
    use RuleIn;
    use Label;
    use Helpers;

    case Sent = 'sent';
    case Delivered = 'delivered';

    public function isDelivered(): bool
    {
        return $this === self::Delivered;
    }
}
