<?php

namespace App\Enums\Orders\Parts;

use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

/**
 * @method static string BS()
 * @method static string Amazon()
 * @method static string Haulk_Depot()
 */
enum OrderSource: string {

    use InvokableCases;
    use RuleIn;
    use Label;

    case BS = 'bs';
    case Amazon = 'amazon';
    case Haulk_Depot = 'haulk_depot';

    public function isHaulkDepot()
    {
        return $this->is(self::Haulk_Depot);
    }

    public function is(OrderSource $value): bool
    {
        return $this->value == $value->value;
    }
}
