<?php

namespace App\Enums\Catalog\Products;

use Core\Enums\BaseEnum;

/**
 * @method static static OLMO()
 * @method static static COOPER()
 */
class ProductOwnerType extends BaseEnum
{
    public const OLMO   = 'olmo';
    public const COOPER = 'cooper';

    public function isOlmo(): bool
    {
        return $this->is(self::OLMO());
    }

    public function isCooper(): bool
    {
        return $this->is(self::COOPER());
    }
}
