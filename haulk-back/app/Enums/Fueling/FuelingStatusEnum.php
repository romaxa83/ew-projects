<?php

namespace App\Enums\Fueling;

use App\Enums\BaseEnum;

/**
 * @method static static PAID()
 * @method static static DUE()
 */

class FuelingStatusEnum extends BaseEnum
{
    public const PAID   = 'paid';
    public const DUE = 'due';
}

