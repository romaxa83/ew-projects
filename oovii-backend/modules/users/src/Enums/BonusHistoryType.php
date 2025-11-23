<?php

namespace WezomCms\Users\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

class BonusHistoryType extends Enum implements LocalizedEnum
{
    public const ACCRUAL = 'accrual';
    public const USE = 'use';
    public const ADJUSTMENT_PLUS = 'adjustment_plus';
    public const ADJUSTMENT_MINUS = 'adjustment_minus';

    /**
     * Get the default localization key
     *
     * @return string
     */
    /*public static function getLocalizationKey(): string
    {
        return 'cms-users::' . app('side') . '.gender';
    }*/

    public function isPositive(): bool
    {
        return $this->in([self::ACCRUAL, self::ADJUSTMENT_PLUS]);
    }

    public function isNegative(): bool
    {
        return $this->in([self::USE, self::ADJUSTMENT_MINUS]);
    }
}
