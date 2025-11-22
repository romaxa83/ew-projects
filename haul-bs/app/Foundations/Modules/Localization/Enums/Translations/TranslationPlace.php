<?php

namespace App\Foundations\Modules\Localization\Enums\Translations;

use App\Foundations\Enums\BaseEnum;

/**
 * @method static static SITE()
 * @method static static SYS()
 */

class TranslationPlace extends BaseEnum
{
    public const SITE = 'site';
    public const SYS = 'sys';

    public function isSys(): bool
    {
        return $this->is(self::SYS());
    }
}
