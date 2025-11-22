<?php

namespace App\Enums\Catalog\Products;

use Core\Enums\BaseEnum;

/**
 * @method static static SINGLE()
 * @method static static MULTI()
 */
class ProductUnitSubType extends BaseEnum
{
    public const SINGLE = 'single';
    public const MULTI  = 'multi';

    public function isSingle(): bool
    {
        return $this->is(self::SINGLE());
    }

    public function isMulti(): bool
    {
        return $this->is(self::MULTI());
    }
}
