<?php

namespace App\Enums\Solutions;

use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * Class SolutionZonesEnum
 * @package App\Enums\Solutions
 *
 * @method static static SINGLE()
 * @method static static MULTI()
 */
class SolutionZoneEnum extends BaseEnum implements LocalizedEnum
{
    public const SINGLE = 'SINGLE';
    public const MULTI = 'MULTI';

    public function isSingle(): bool
    {
        return $this->is(self::SINGLE);
    }

    public function isMulti(): bool
    {
        return $this->is(self::MULTI);
    }
}
