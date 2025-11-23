<?php

namespace App\Enums\Orders;

use App\Enums\Base\InvokableCases;

/**
 * @method static Local()
 * @method static Interstate()
 * @method static Intrastate()
 */

enum EstimateType: string {

    use InvokableCases;

    case Local = "local";
    case Interstate = "interstate";
    case Intrastate = "intrastate";
}
