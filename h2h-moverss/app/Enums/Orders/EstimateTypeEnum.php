<?php

namespace App\Enums\Orders;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;
use App\Enums\Base\RuleIn;

/**
 * @method static Local()
 * @method static Intrastate()
 * @method static Interstate()
 */

enum EstimateTypeEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;
    use RuleIn;

    case Local = "local";
    case Intrastate = "intrastate";
    case Interstate = "interstate";
}
