<?php

namespace App\Enums\Fueling;

use App\Enums\BaseEnum;

/**
 * @method static static IMPORT()
 * @method static static MANUALLY()
 */

class FuelingSourceEnum extends BaseEnum
{
    public const IMPORT   = 'import';
    public const MANUALLY = 'manually';
}

