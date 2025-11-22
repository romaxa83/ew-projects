<?php

namespace App\Enums\Fueling;

use App\Enums\BaseEnum;

/**
 * @method static static ACTIVE()
 * @method static static INACTIVE()
 * @method static static DELETED()
 */

class FuelCardStatusEnum extends BaseEnum
{
    public const ACTIVE   = 'active';
    public const INACTIVE = 'inactive';
    public const DELETED = 'deleted';
}

