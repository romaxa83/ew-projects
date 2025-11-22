<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\Helpers;
use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string USPS()
 * @method static string UPS()
 * @method static string Fedex()
 * @method static string LTL()
 * @method static string Our_delivery()
 */

enum DeliveryMethod: string {

    use InvokableCases;
    use RuleIn;
    use Label;
    use Helpers;

    case USPS = 'usps';
    case UPS = 'ups';
    case Fedex = 'fedex';
    case LTL = 'ltl';
    case Our_delivery = 'our_delivery';
}
