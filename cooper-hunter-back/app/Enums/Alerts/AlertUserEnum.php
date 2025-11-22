<?php

namespace App\Enums\Alerts;

use Core\Enums\BaseEnum;

/**
 * Class AlertUserEnum
 * @package App\Enums\Alerts
 *
 * @method static static REGISTRATION()
 * @method static static CUSTOM()
 */
final class AlertUserEnum extends BaseEnum
{
    public const REGISTRATION = 'registration';
    public const CUSTOM = 'custom';

    public static function getFrontList(): array
    {
        return [
            self::CUSTOM
        ];
    }

    public static function getBackList(): array
    {
        return [
            self::REGISTRATION
        ];
    }
}
