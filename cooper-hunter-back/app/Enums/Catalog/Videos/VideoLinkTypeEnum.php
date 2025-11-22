<?php

namespace App\Enums\Catalog\Videos;

use Core\Enums\BaseEnum;

/**
 * @method static static COMMON()
 * @method static static SUPPORT()
 * @method static static COMMERCIAL()
 */
class VideoLinkTypeEnum extends BaseEnum
{
    public const COMMON     = 'common';
    public const SUPPORT    = 'support';
    public const COMMERCIAL = 'commercial';
}
