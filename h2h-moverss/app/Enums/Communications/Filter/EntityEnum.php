<?php

namespace App\Enums\Communications\Filter;

use App\Enums\Base\InvokableCases;
use App\Enums\Base\RuleIn;

/**
 * @method static All()
 * @method static Calls()
 * @method static Emails()
 */

enum EntityEnum: string {

    use InvokableCases;
    use RuleIn;

    case All  = "all";
    case Calls  = "calls";
    case Emails = "emails";
}
