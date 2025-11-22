<?php

namespace App\Enums\Solutions;

use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;
use Illuminate\Support\Str;

/**
 * Class SolutionClimateZoneEnum
 * @package App\Enums\Solutions
 *
 * @method static static HOT()
 * @method static static MODERATE()
 * @method static static COLD()
 */
class SolutionClimateZoneEnum extends BaseEnum implements LocalizedEnum
{
    public const HOT = 'HOT';
    public const MODERATE = 'MODERATE';
    public const COLD = 'COLD';

    public function getSlug(): string
    {
        return Str::slug($this->key);
    }
}
