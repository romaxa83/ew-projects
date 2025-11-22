<?php

namespace App\Enums\Menu;

use Core\Enums\BaseEnum;

/**
 * @method static static HEADER()
 * @method static static FOOTER()
 */
class MenuPositionEnum extends BaseEnum
{
    public const HEADER = 'HEADER';
    public const FOOTER = 'FOOTER';
}
