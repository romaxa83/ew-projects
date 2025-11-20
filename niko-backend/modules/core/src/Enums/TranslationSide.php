<?php

namespace WezomCms\Core\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

final class TranslationSide extends Enum implements LocalizedEnum
{
    public const ADMIN = 'admin';
    public const SITE = 'site';

    /**
     * Get the default localization key
     *
     * @return string
     */
    public static function getLocalizationKey(): string
    {
        return 'cms-core::admin.translation_side';
    }
}
