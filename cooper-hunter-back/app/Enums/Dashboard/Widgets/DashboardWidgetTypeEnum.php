<?php

namespace App\Enums\Dashboard\Widgets;

use Core\Enums\BaseEnum;

/**
 * @method static static NEW()
 * @method static static TOTAL()
 */
class DashboardWidgetTypeEnum extends BaseEnum
{
    public const NEW = 'new';
    public const TOTAL = 'total';
}