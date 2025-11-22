<?php

namespace App\Enums\Alerts;

use App\Contracts\Alerts\AlertEnum;
use Core\Enums\BaseEnum;

/**
 * Class AlertModelEnum
 * @package App\Enums\Alerts
 *
 * @method static static CREATE()
 * @method static static CHANGE_STATUS()
 */
final class AlertOrderEnum extends BaseEnum implements AlertEnum
{
    public const CREATE = 'create';
    public const CHANGE_STATUS = 'change_status';

    public static function getFrontList(): array
    {
        return self::getValues();
    }

    public static function getBackList(): array
    {
        return [
            self::CREATE
        ];
    }
}
