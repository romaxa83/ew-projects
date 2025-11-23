<?php

namespace App\Enums\Communications\Filter;

use App\Enums\Base\InvokableCases;
use App\Enums\Base\RuleIn;

/**
 * @method static Any()
 * @method static Today()
 * @method static Yesterday()
 * @method static Last_7_days()
 * @method static Last_30_days()
 */

enum PeriodEnum: string {

    use InvokableCases;
    use RuleIn;

    case Any          = "any";
    case Today        = "today";
    case Yesterday    = "yesterday";
    case Last_7_days  = "last7days";
    case Last_30_days = "last30days";
}

