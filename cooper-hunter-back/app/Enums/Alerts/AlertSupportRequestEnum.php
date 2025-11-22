<?php

namespace App\Enums\Alerts;

use Core\Enums\BaseEnum;

/**
 * Class AlertSupportRequestEnum
 * @package App\Enums\Alerts
 *
 * @method static static NEW_REQUEST()
 * @method static static NEW_MESSAGE()
 * @method static static CLOSE()
 */
final class AlertSupportRequestEnum extends BaseEnum
{
    public const NEW_REQUEST = 'new_request';
    public const NEW_MESSAGE = 'new_message';
    public const CLOSE = 'close';

    public static function getFrontList(): array
    {
        return [
            self::NEW_MESSAGE,
            self::CLOSE,
        ];
    }

    public static function getBackList(): array
    {
        return [
            self::NEW_REQUEST,
            self::NEW_MESSAGE
        ];
    }
}
