<?php

namespace App\Enums\Catalog\Products;

use Core\Enums\BaseEnum;

/**
 * @method static static INDOOR()
 * @method static static OUTDOOR()
 * @method static static MONOBLOCK()
 * @method static static ACCESSORY()
 * @method static static SPARES()
 */
class ProductUnitType extends BaseEnum
{
    public const INDOOR    = 'indoor';
    public const OUTDOOR   = 'outdoor';
    public const MONOBLOCK = 'monoblock';
    public const ACCESSORY = 'accessory';
    public const SPARES    = 'spare_part';
}
