<?php

namespace WezomCms\ProductReviews\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

class SortVariants extends Enum implements LocalizedEnum
{
    public const TOP = 'top';
    public const LATEST = 'latest';
    public const OLDEST = 'oldest';

    /**
     * Get the default localization key
     *
     * @return string
     */
    public static function getLocalizationKey(): string
    {
        return 'cms-product-reviews::' . app('side') . '.sort_variants';
    }
}
