<?php

namespace App\Enums\Warranties;

use Core\Enums\BaseEnum;

/**
 * @method static static RESIDENTIAL()
 * @method static static COMMERCIAL()
 */
class WarrantyType extends BaseEnum
{
    public const RESIDENTIAL = 'residential';
    public const COMMERCIAL = 'commercial';

}
