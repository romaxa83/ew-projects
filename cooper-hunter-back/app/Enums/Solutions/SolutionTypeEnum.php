<?php

namespace App\Enums\Solutions;

use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * Class SolutionProductEnum
 * @package App\Enums\Solutions
 *
 * @method static static INDOOR()
 * @method static static OUTDOOR()
 * @method static static LINE_SET()
 */
class SolutionTypeEnum extends BaseEnum implements LocalizedEnum
{
    public const INDOOR = 'INDOOR';
    public const OUTDOOR = 'OUTDOOR';
    public const LINE_SET = 'LINE_SET';

    public function isIndoor(): bool
    {
        return $this->is(self::INDOOR);
    }

    public function isOutdoor(): bool
    {
        return $this->is(self::OUTDOOR);
    }

    public function isLineSet(): bool
    {
        return $this->is(self::LINE_SET);
    }
}
