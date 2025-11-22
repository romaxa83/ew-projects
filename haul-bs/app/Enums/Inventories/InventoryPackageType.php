<?php

namespace App\Enums\Inventories;

use App\Foundations\Enums\Traits\InvokableCases;
use App\Foundations\Enums\Traits\Label;
use App\Foundations\Enums\Traits\RuleIn;

enum InventoryPackageType: string {

    use InvokableCases;
    use RuleIn;
    use Label;

    case Custom = "custom_package";
    case Carrier = "carrier_package";
}
