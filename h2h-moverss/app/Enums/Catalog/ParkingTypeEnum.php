<?php

namespace App\Enums\Catalog;

use App\Enums\Base\ForSelect;
use App\Enums\Base\InvokableCases;
use App\Enums\Base\Label;

/**
 * @method static No_parking()
 * @method static Loading_dock()
 * @method static Parking_zone()
 * @method static Street_parking()
 * @method static Alley_parking()
 */

enum ParkingTypeEnum: string
{
    use InvokableCases;
    use Label;
    use ForSelect;

    case No_parking     = "1";
    case Loading_dock   = "2";
    case Parking_zone   = "3";
    case Street_parking = "4";
    case Alley_parking  = "5";
}
