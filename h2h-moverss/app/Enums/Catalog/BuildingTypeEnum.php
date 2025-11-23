<?php

namespace App\Enums\Catalog;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;

/**
 * @method static Home()
 * @method static Apartment()
 * @method static Storage()
 * @method static Office()
 */

enum BuildingTypeEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;

    case Home      = "1";
    case Apartment = "2";
    case Storage   = "3";
    case Office    = "4";
}
