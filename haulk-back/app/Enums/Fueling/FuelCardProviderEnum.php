<?php

namespace App\Enums\Fueling;

use App\Enums\BaseEnum;

/**
 * @method static static EFS()
 * @method static static QUIKQ()
 */

class FuelCardProviderEnum extends BaseEnum
{
    public const EFS   = 'efs';
    public const QUIKQ = 'quikq';
}

