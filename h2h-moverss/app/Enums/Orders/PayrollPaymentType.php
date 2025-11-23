<?php

namespace App\Enums\Orders;

use App\Enums\Base\InvokableCases;
use App\Enums\Base\RuleIn;

/**
 * @method static Cash()
 * @method static Zelle()
 * @method static CC()
 */

enum PayrollPaymentType: string {

    use InvokableCases;
    use RuleIn;

    case Cash  = "cash";
    case Zelle = "zelle";
    case CC    = "cc";
}
