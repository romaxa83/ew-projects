<?php

namespace App\Enums\Tags;

use App\Foundations\Enums\BaseEnum;

/**
 * @method static static TRUCKS_AND_TRAILER()
 * @method static static CUSTOMER()
 */

class TagType extends BaseEnum
{
    public const TRUCKS_AND_TRAILER = 'trucks_and_trailer';
    public const CUSTOMER = 'customer';

    public function isCustomer(): bool
    {
        return $this->is(self::CUSTOMER());
    }

    public function isTrucksAndTrailer(): bool
    {
        return $this->is(self::TRUCKS_AND_TRAILER());
    }
}
