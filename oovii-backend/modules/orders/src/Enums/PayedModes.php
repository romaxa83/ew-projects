<?php

namespace WezomCms\Orders\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

final class PayedModes extends Enum implements LocalizedEnum
{
    public const MANUAL = 'manual';
    public const AUTO = 'auto';

    /**
     * Get the default localization key
     *
     * @return string
     */
    public static function getLocalizationKey(): string
    {
        return 'cms-orders::' . app('side') . '.payed_modes';
    }
}
