<?php

namespace App\Enums\Solutions;

use BenSampo\Enum\Contracts\LocalizedEnum;
use Core\Enums\BaseEnum;

/**
 * Class SolutionSeriesEnum
 * @package App\Enums\Solutions
 *
 * @method static static SOPHIA()
 * @method static static SOPHIA_D()
 * @method static static HYPER()
 */
class SolutionSeriesEnum extends BaseEnum implements LocalizedEnum
{
    public const SOPHIA = 'SOPHIA';
    public const SOPHIA_D = 'SOPHIA_D';
    public const HYPER = 'HYPER';
}
