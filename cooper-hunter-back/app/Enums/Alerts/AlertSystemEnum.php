<?php

namespace App\Enums\Alerts;

use Core\Enums\BaseEnum;

/**
 * Class AlertSystemEnum
 * @package App\Enums\Alerts
 *
 * @method static static WARRANTY_STATUS()
 */
final class AlertSystemEnum extends BaseEnum
{
    public const WARRANTY_STATUS = 'warranty_status';

    public static function getFrontList(): array
    {
        return self::getValues();
    }

    public static function getBackList(): array
    {
        return [];
    }
}
