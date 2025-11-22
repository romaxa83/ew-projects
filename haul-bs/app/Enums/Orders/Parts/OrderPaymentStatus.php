<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\Helpers;
use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string Paid()
 * @method static string Not_paid()
 * @method static string Refunded()
 */
enum OrderPaymentStatus: string {

    use InvokableCases;
    use RuleIn;
    use Label;
    use Helpers;

    case Paid = 'paid';
    case Not_paid = 'not_paid';
    case Refunded = 'refunded';
}
