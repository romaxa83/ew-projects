<?php

namespace App\Enums\Commercial\Commissioning;

use Core\Enums\BaseEnum;

/**
 * @method static static COMMISSIONING()
 * @method static static PRE_COMMISSIONING()
 */
class ProtocolType extends BaseEnum
{
    public const PRE_COMMISSIONING = 'pre_commissioning';
    public const COMMISSIONING     = 'commissioning';
}
